<?php

namespace App\Controller\Home;

use App\Entity\Command;
use App\Entity\Pharmacy;
use App\Form\CommandType;
use App\Form\ProductSearchType;
use App\Repository\PharmacyRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HomeController
 * @package App\Controller\Home
 */
class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param PharmacyRepository $repository
     * @param Request $request
     * @param ProductRepository $productRepository
     * @return Response
     */
    public function index(PharmacyRepository $repository, Request $request, ProductRepository $productRepository): Response
    {
        $pharmacies = $repository->findBy([], ['createdAt' => 'DESC']);

        $product = null;
        $form = $this->createForm(ProductSearchType::class);
        $search = $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $product = $productRepository->search($search->get('mots')->getData());
        }
        return $this->render('home/pages/index.html.twig',[
            'search' => $form->createView(),
            'product' => $product,
            'pharmacies' => $pharmacies
        ]);
    }

    /**
     * @Route("/pharcmacy/{id<[0-9]+>}" , name="pharmacy")
     * @param Pharmacy $pharmacy
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function pharmacy(Pharmacy $pharmacy, Request $request, EntityManagerInterface $entityManager)
    {
        $command = new Command();
        $form = $this->createForm(CommandType::class, $command,[
            'method' => 'post'
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager->persist($command);
            $entityManager->flush();

            return $this->redirectToRoute('pharmacy');
        }
        return $this->render('home/pages/pharmacy.html.twig',[
            'pharmacy' => $pharmacy,
            'command' => $form->createView()
        ]);
    }
}
