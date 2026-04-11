<?php

namespace App\Controller;

use App\Entity\Province;
use App\Form\ProvinceType;
use App\Repository\ProvinceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/province')]
final class ProvinceController extends AbstractController
{
    #[Route(name: 'app_province_index', methods: ['GET'])]
    public function index(ProvinceRepository $provinceRepository): Response
    {
        return $this->render('province/index.html.twig', [
            'provinces' => $provinceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_province_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $province = new Province();
        $form = $this->createForm(ProvinceType::class, $province);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($province);
            $entityManager->flush();

            return $this->redirectToRoute('app_province_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('province/new.html.twig', [
            'province' => $province,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_province_show', methods: ['GET'])]
    public function show(Province $province): Response
    {
        return $this->render('province/show.html.twig', [
            'province' => $province,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_province_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Province $province, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProvinceType::class, $province);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_province_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('province/edit.html.twig', [
            'province' => $province,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_province_delete', methods: ['POST'])]
    public function delete(Request $request, Province $province, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$province->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($province);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_province_index', [], Response::HTTP_SEE_OTHER);
    }
}
