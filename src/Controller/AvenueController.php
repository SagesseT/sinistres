<?php

namespace App\Controller;

use App\Entity\Avenue;
use App\Form\AvenueType;
use App\Repository\AvenueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/avenue')]
class AvenueController extends AbstractController
{
    // ==============================
    // 📥 IMPORT CSV (IMPORTANT EN HAUT)
    // ==============================
    #[Route('/import', name: 'app_avenue_import')]
    public function import(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {

            $file = $request->files->get('file');

            if (!$file) {
                $this->addFlash('danger', '❌ Aucun fichier sélectionné');
                return $this->redirectToRoute('app_avenue_import');
            }

            if (($handle = fopen($file->getPathname(), 'r')) !== false) {

                $success = 0;
                $error = 0;

                while (($data = fgetcsv($handle, 1000, ";")) !== false) {

                    // 🔎 Vérifier colonne
                    if (count($data) < 1) {
                        $error++;
                        continue;
                    }

                    $nom = trim($data[0]);

                    if (!$nom) {
                        $error++;
                        continue;
                    }

                    try {
                        // Vérifier si existe déjà
                        $exist = $em->getRepository(Avenue::class)->findOneBy([
                            'nom' => $nom
                        ]);

                        if (!$exist) {
                            $avenue = new Avenue();
                            $avenue->setNom($nom);

                            $em->persist($avenue);
                            $success++;
                        }

                    } catch (\Exception $e) {
                        $error++;
                    }
                }

                fclose($handle);
                $em->flush();

                $this->addFlash('success', "✅ Import terminé : $success ajout(s), $error erreur(s)");

                return $this->redirectToRoute('app_avenue_index');
            }
        }

        return $this->render('avenue/import.html.twig');
    }

    // ==============================
    // 📋 LISTE + RECHERCHE
    // ==============================
    #[Route('/', name: 'app_avenue_index')]
    public function index(AvenueRepository $repo, Request $request): Response
    {
        $q = $request->query->get('q');

        if ($q) {
            $avenues = $repo->createQueryBuilder('a')
                ->where('a.nom LIKE :q')
                ->setParameter('q', "%$q%")
                ->orderBy('a.id', 'DESC')
                ->getQuery()
                ->getResult();
        } else {
            $avenues = $repo->findBy([], ['id' => 'DESC']);
        }

        return $this->render('avenue/index.html.twig', [
            'avenues' => $avenues,
        ]);
    }

    // ==============================
    // ➕ CREATION
    // ==============================
    #[Route('/new', name: 'app_avenue_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $avenue = new Avenue();

        $form = $this->createForm(AvenueType::class, $avenue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($avenue);
            $em->flush();

            $this->addFlash('success', '✅ Avenue "' . $avenue->getNom() . '" ajoutée avec succès');

            return $this->redirectToRoute('app_avenue_index');
        }

        return $this->render('avenue/new.html.twig', [
            'form' => $form
        ]);
    }

    // ==============================
    // 👁️ DETAILS
    // ==============================
    #[Route('/{id}', name: 'app_avenue_show', requirements: ['id' => '\d+'])]
    public function show(Avenue $avenue): Response
    {
        return $this->render('avenue/show.html.twig', [
            'avenue' => $avenue
        ]);
    }

    // ==============================
    // ✏️ MODIFICATION
    // ==============================
    #[Route('/{id}/edit', name: 'app_avenue_edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Avenue $avenue, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AvenueType::class, $avenue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();

            $this->addFlash('success', '✏️ Avenue "' . $avenue->getNom() . '" modifiée avec succès');

            return $this->redirectToRoute('app_avenue_index');
        }

        return $this->render('avenue/edit.html.twig', [
            'form' => $form,
            'avenue' => $avenue
        ]);
    }

    // ==============================
    // 🗑️ SUPPRESSION
    // ==============================
    #[Route('/{id}', name: 'app_avenue_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Avenue $avenue, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$avenue->getId(), $request->request->get('_token'))) {

            $nom = $avenue->getNom();

            $em->remove($avenue);
            $em->flush();

            $this->addFlash('success', '🗑️ Avenue "' . $nom . '" supprimée avec succès');
        }

        return $this->redirectToRoute('app_avenue_index');
    }
}