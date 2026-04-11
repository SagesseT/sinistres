<?php

namespace App\Controller;

use App\Entity\Parcelle;
use App\Entity\Document;
use App\Form\ParcelleType;
use App\Repository\ParcelleRepository;
use App\Repository\VictimeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/parcelle')]
class ParcelleController extends AbstractController
{
    #[Route('/', name: 'app_parcelle_index')]
    public function index(ParcelleRepository $repo, Request $request): Response
    {
        $q = $request->query->get('q');

        if ($q) {
            $parcelles = $repo->createQueryBuilder('p')
                ->join('p.victime', 'v')
                ->where('v.nom LIKE :q OR v.postnom LIKE :q OR p.numero LIKE :q')
                ->setParameter('q', "%$q%")
                ->orderBy('p.id', 'DESC')
                ->getQuery()
                ->getResult();
        } else {
            $parcelles = $repo->findBy([], ['id' => 'DESC']);
        }

        return $this->render('parcelle/index.html.twig', [
            'parcelles' => $parcelles,
        ]);
    }
    #[Route('/new', name: 'app_parcelle_new')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        VictimeRepository $victimeRepository
    ): Response {

        $parcelle = new Parcelle();

        // 🔥 récupération ID
        $victimeId = $request->query->get('victime');

        if ($victimeId) {
            $victime = $victimeRepository->find($victimeId);

            if ($victime) {
                $parcelle->setVictime($victime);
            }
        }

        $form = $this->createForm(ParcelleType::class, $parcelle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $parcelle->setUser($this->getUser());
            $parcelle->setDatecreated(new \DateTime());

            $em->persist($parcelle);
            $em->flush();

            return $this->redirectToRoute('app_parcelle_index');
        }

        return $this->render('parcelle/new.html.twig', [
            'form' => $form,
            'parcelle' => $parcelle
        ]);
    }

    #[Route('/{id}', name: 'app_parcelle_show')]
    public function show(Parcelle $parcelle): Response
    {
        return $this->render('parcelle/show.html.twig', [
            'parcelle' => $parcelle,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_parcelle_edit')]
public function edit(Request $request, Parcelle $parcelle, EntityManagerInterface $em): Response
{
    $form = $this->createForm(ParcelleType::class, $parcelle);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        foreach ($form->get('documents') as $documentForm) {

            $document = $documentForm->getData();

            // 🔥 fichier uploadé (IMPORTANT)
            $uploadedFile = $documentForm->get('fichier')->getData();

            if ($uploadedFile) {

                // ❌ ancien fichier supprimé (optionnel mais pro)
                if ($document->getFichier()) {
                    $oldPath = $this->getParameter('documents_directory') . '/' . $document->getFichier();
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                // ✔ nouveau fichier
                $filename = uniqid() . '.' . $uploadedFile->guessExtension();

                $uploadedFile->move(
                    $this->getParameter('documents_directory'),
                    $filename
                );

                $document->setFichier($filename);
            }

            // date automatique
            if (!$document->getDateUpload()) {
                $document->setDateUpload(new \DateTime());
            }

            $document->setParcelle($parcelle);
        }

        $em->flush();

        $this->addFlash('success', '✏️ Parcelle modifiée avec succès');

        return $this->redirectToRoute('app_parcelle_index');
    }

    return $this->render('parcelle/edit.html.twig', [
        'form' => $form,
        'parcelle' => $parcelle
    ]);
}

    #[Route('/{id}', name: 'app_parcelle_delete', methods: ['POST'])]
    public function delete(Request $request, Parcelle $parcelle, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $parcelle->getId(), $request->request->get('_token'))) {

            $em->remove($parcelle);
            $em->flush();

            $this->addFlash('success', '🗑️ Parcelle supprimée avec succès');
        }

        return $this->redirectToRoute('app_parcelle_index');
    }
}
