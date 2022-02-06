<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderSuccessController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
 
    /**
     * @Route("/commande/merci/{stripeSessionId}", name="order_success")
     */
    public function index(Cart $cart, $stripeSessionId): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);
       
        if (!$order  || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('home');
        }

        if ($order->getState() == 0) {
            // Vider la session "cart"
            $cart->remove();
            // Modifier le statut is paid de la commande en mettant 1
            $order->setState(1);
            $this->entityManager->flush();

            // Envoyer un email à notre client pour lui confirmer sa commande 
            
            $mail = new Mail();
            $content = "Bonjour" . $order->getUser()->getFirstname() . ", <br> Merci pour votre commande. <br><br>Lorem ipsum dolor sit amet consectetur adipisicing elit. Laboriosam temporibus molestiae consequatur repellat nemo fuga quod, expedita, porro tenetur necessitatibus sequi nulla magni nisi quasi blanditiis culpa quisquam aliquid. Minima. ";
            $mail->send($order->getUser()->getEmail(), $order->getUser()->Firstname(), "Votre commande La Boutique Française est bien validée.", $content);
        }
        // afficher quelques informations de la commande de l'utilisateur
        
        
        return $this->render('order_success/index.html.twig', [
            'order' => $order
        ]);
    }
}
