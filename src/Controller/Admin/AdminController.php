<?php

namespace App\Controller\Admin;

use App\Entity\Command;
use App\Entity\Pharmacy;
use App\Entity\Product;
use App\Form\ProductSearchType;
use App\Form\ProductType;
use App\Repository\CommandRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin", name="admin_")
 * Class AdminController
 * @package App\Controller\Admin
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        return $this->render('admin/pages/index.html.twig');
    }

    /**
     * @Route("/gestion", name="gestion")
     * @return Response
     */
    public function gestion(){
        return $this->render('admin/pages/gestion.html.twig');
    }

    /**
     * @Route("/traitement", name="traitement")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param ProductRepository $repository
     * @param CommandRepository $commandRepository
     * @return Response
     */
    public function traitement(Request $request,
                               EntityManagerInterface $entityManager,
                               ProductRepository $repository,
                               CommandRepository $commandRepository)
    {
        $product = new Product();
        $form_product = $this->createForm(ProductType::class, $product);
        $form_product->handleRequest($request);
        if($form_product->isSubmitted() && $form_product->isValid()){
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('admin_traitement');
        }

        $form_search = $this->createForm(ProductSearchType::class);
        $search = $form_search->handleRequest($request);

        if($form_search->isSubmitted() && $form_search->isValid()){
            $repository->search($search->get('mots')->getData());
        }

        return $this->render('admin/pages/traitement.html.twig',[
            'product' => $form_product->createView(),
            'search' => $form_search->createView(),
            'commands' => $commandRepository->findBy([], ['createdAt'=> 'DESC'])
        ]);
    }

    /**
     * @Route("/pharmacy/{id<[0-9]+>}", name="show", methods={"GET"})
     * @param Pharmacy $pharmacy
     * @return Response
     */
    public function pharmacyShow(Pharmacy $pharmacy){
        return $this->render('admin/pages/pharmacy_show.html.twig',[
            'pharmacy' => $pharmacy
        ]);
    }

    /**
     * @Route("/pharmacy/{id<[0-9]+>}/edit", name="edit", methods={"GET", "POST"})
     * @param Pharmacy $pharmacy
     * @return Response
     */
    public function pharmacyEdit(Pharmacy $pharmacy){
        return $this->render('admin/pages/pharmacy_edit.html.twig',[
            'pharmacy' => $pharmacy
        ]);
    }

    /**
     * @Route("/pharmacy/{id<[0-9]+>}/delete", name="delete", methods={"DELETE"})
     * @param Pharmacy $pharmacy
     * @param EntityManagerInterface $entityManager
     * @return RedirectResponse
     */
    public function pharmacyDelete(Pharmacy  $pharmacy, EntityManagerInterface $entityManager){

        $entityManager->remove($pharmacy);
        $entityManager->flush();

        return $this->redirectToRoute("home");
    }
}
