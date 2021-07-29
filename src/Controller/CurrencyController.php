<?php

namespace App\Controller;

use App\Entity\Currency;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use App\Utils\Interfaces\CacheInterface;

class CurrencyController extends AbstractController
{
    public $perPage;

    public function __construct(string $perPage)
    {
        $this->perPage = $perPage;
    }

    /**
     * Renders list of currencies
     *
     * @Route("/", name="currency_index")
     */
    public function index(Request $request, PaginatorInterface $paginator, CacheInterface $cache): Response
    {
        $cache = $cache->cache;
        $cachedItem = $cache->getItem("currency_index_page_" . $request->query->get('page'));
        $cachedItem->expiresAfter(3600 * 24);

        if (!$cachedItem->isHit()) {
            $currencies = $this->getDoctrine()
                ->getRepository(Currency::class)
                ->findActiveCurrencies();

            $pagination = $paginator->paginate(
                $currencies,
                $request->query->getInt('page', 1),
                $this->perPage
            );

            $response = $this->render('currency/index.html.twig', [
                'currencies' => $pagination,
            ]);

            $cachedItem->set($response);
            $cache->save($cachedItem);
        }

        return $cachedItem->get();
    }

    /**
     * Renders currency page with rates
     *
     * @Route("/currency/{slug}", name="currency_show")
     */
    public function show($slug, Request $request, PaginatorInterface $paginator, CacheInterface $cache): Response
    {
        $cache = $cache->cache;
        $cachedItem = $cache->getItem("currency_show_" . $slug . "_page_" . $request->query->get('page'));
        $cachedItem->expiresAfter(3600 * 24);

        if (!$cachedItem->isHit()) {
            $currency = $this->getDoctrine()
                ->getRepository(Currency::class)
                ->findOneBy(['slug' => $slug]);

            if (!$currency) {
                return $this->redirect($this->generateUrl('currency_index'));
            }

            $rates = $currency->getCurrencyRates();
            $pagination = $paginator->paginate(
                $rates,
                $request->query->getInt('page', 1),
                $this->perPage
            );

            $response = $this->render('currency/show.html.twig', [
                'currency' => $currency,
                'rates' => $pagination,
            ]);

            $cachedItem->set($response);
            $cache->save($cachedItem);
        }

        return $cachedItem->get();
    }
}