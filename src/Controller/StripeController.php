<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{
    #[Route('/commande/create_session/{reference}', name: 'stripe_create_session')]
    public function index(EntityManagerInterface $entityManager, Cart $cart, $reference): Response
    {
        $product_for_stripe = [];
        $YOUR_DOMAIN = 'http://localhost:8000'; 

        $order = $entityManager->getRepository(Order::class)->findOneByReference($reference);

        if (!$order) {
            return $this->redirectToRoute('order');
        }

        foreach ($order->getOrderDetails()->getValues() as $product) {
            $product_object = $entityManager->getRepository(Product::class)->findOneByName($product->getProduct());
            $product_for_stripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $product->getPrice(),
                    'product_data' => [
                        'name' => $product->getProduct(),
                        'images' => [$YOUR_DOMAIN."/uploads".$product_object->getillustration()],
                    ],
                ],
                'quantity' => $product->getQuantity(),
            ];
        }

        $product_for_stripe[] = [
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => $order->getCarrierPrice(),
                'product_data' => [
                    'name' => $order->getCarrierName(),
                    'images' => [
                        $YOUR_DOMAIN,
                    ]
                ]
            ],
            'quantity' => 1,
        ];

        Stripe::setApiKey('sk_test_51KNiq6IN60LStFKgjOA66v3c3g8Q3fGaQ7vce9fwvBMXeQD1Z4uw3lKXlvJouHjyrPOqaudCYxbEBgcAPknIwAbP00CXgogunV');

        $checkout_session = Session::create([
            'customer_email' => $order->getUser()->getEmail(),
            'payment_method_types' => ['card'],
            'line_items' => [[
              $product_for_stripe
            ]],
            'mode' => 'payment',
              'success_url' => $YOUR_DOMAIN . '/commande/merci/{CHECKOUT_SESSION_ID}',
              'cancel_url' => $YOUR_DOMAIN . '/commande/erreur/{CHECKOUT_SESSION_ID}',
          ]);

          $order->setStripeSessionId($checkout_session->id);
          $entityManager->flush();

          return $this->redirect($checkout_session->url);
    }
}
