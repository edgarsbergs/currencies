<?php

namespace App\Controller;

use App\Entity\Currency;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\CurrencyRate;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class CurrencyApiController extends AbstractController
{
    private $entityManager;
    protected $apiData;
    protected $currencyApiUrl;
    protected $allowedModes;
    protected $mode; // which days do we need.
    protected $currenciesDays; // contains extracted currency data

    public function __construct()
    {
        $this->currencyApiUrl = $_ENV['CURRENCY_API_URL'];
        $this->allowedModes = ['today', 'all'];
    }

    public function process($mode)
    {
        if (!in_array($mode, $this->allowedModes)) {
            throw new \Exception("Provide one of following: " . implode(', ', $this->allowedModes));
        }

        $this->mode = $mode;

        $this->getApiData();
        $this->parseDays();
        $this->saveToDatabase();

        return true;
    }

    /* for calling from web browser */
    public function index($mode)
    {
        $this->__construct();
        $this->process($mode);
        $this->clearCache();

        return new Response('success');
    }

    public function getApiData()
    {
        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL, $this->currencyApiUrl);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        try {
            $output = curl_exec($ch);
        } catch (exception $e) {
            echo $e->getMessage();
        }

        if (!$output) {
            throw new \Exception(curl_error($ch));
        }

        curl_close($ch);

        $this->apiData = simplexml_load_string($output);
    }

    /* parse data & save to array */
    public function parseDays()
    {
        foreach ($this->apiData->channel->item as $item) {
            // figure out which days do we need
            $today = date('d-m-Y');

            if ($this->mode == 'today' && date('d-m-Y', strtotime($item->pubDate)) == $today
                OR $this->mode == 'all')
            {
                // extract currency pairs (currency code & rate)
                preg_match_all('/[a-zA-Z]{3} (\d+.\d+)/', $item->description, $currenciesExtracted);
                $rateValues = [];

                //extract & save : code, rate
                foreach ($currenciesExtracted[0] as $pair) {
                    $pairValues = explode(' ', $pair);
                    $rateValues []= [
                        'code' => strtolower($pairValues[0]),
                        'value' => $pairValues[1],
                    ];
                }

                $this->currenciesDays [] = [
                    'date' => $item->pubDate,
                    'content' => $rateValues,
                ];
            }
        }
    }

    public function saveToDatabase()
    {
        if (!$this->currenciesDays) {
            throw new \Exception('No data');
        }

        foreach ($this->currenciesDays as $day) {
            $date = new \DateTime('@'.strtotime($day['date']));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

            foreach ($day['content'] as $rate) {
                $currency = $entityManager->getRepository(Currency::class)->findOneBy([
                    'slug' => $rate['code'],
                ]);

                $currencyRate = new CurrencyRate;
                $currencyRate->setCurrencyId($currency);
                $currencyRate->setValue($rate['value']);
                $currencyRate->setTimestamp($date);
                $entityManager->persist($currencyRate);
            }

            $entityManager->flush();
            $entityManager->clear();
        }
    }

    public function clearCache(KernelInterface $kernel)
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'cache:clear',
        ]);

        $output = new BufferedOutput();
        $application->run($input, $output);
    }
}