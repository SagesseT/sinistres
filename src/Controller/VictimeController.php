<?php

namespace App\Controller;

use App\Entity\Victime;
use App\Entity\Parcelle;
use App\Entity\Document;
use App\Entity\TypeDocument;
use App\Entity\Avenue;
use App\Form\VictimeType;
use App\Repository\VictimeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/victime')]
class VictimeController extends AbstractController
{
    // =========================
    // 📋 LISTE + RECHERCHE
    // =========================
    #[Route('/', name: 'app_victime_index')]
    public function index(VictimeRepository $repo, Request $request): Response
    {
        $q = $request->query->get('q');

        if ($q) {
            $victimes = $repo->createQueryBuilder('v')
                ->where('v.nom LIKE :q OR v.postnom LIKE :q OR v.prenom LIKE :q')
                ->setParameter('q', "%$q%")
                ->orderBy('v.id', 'DESC')
                ->getQuery()
                ->getResult();
        } else {
            $victimes = $repo->findBy([], ['id' => 'DESC']);
        }

        return $this->render('victime/index.html.twig', [
            'victimes' => $victimes,
        ]);
    }

    // =========================
    // ➕ AJOUT
    // =========================
    #[Route('/new', name: 'app_victime_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $victime = new Victime();
        $form = $this->createForm(VictimeType::class, $victime);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // 📷 Upload photo
            $file = $form->get('photo')->getData();

            if ($file) {
                $filename = uniqid() . '.' . $file->guessExtension();
                $file->move($this->getParameter('uploads_directory'), $filename);
                $victime->setPhoto($filename);
            }

            $victime->setUser($this->getUser());
            $victime->setDateEnregistrement(new \DateTime());
            $victime->setNumeroDossier(uniqid('DOS-'));

            $em->persist($victime);
            $em->flush();

            $this->addFlash('success', '✅ Victime ' . $victime->getNom() . ' ' . $victime->getPrenom() . ' enregistrée avec succès');

            return $this->redirectToRoute('app_victime_index');
        }

        return $this->render('victime/new.html.twig', [
            'form' => $form,
        ]);
    }

    // =========================
    // 📥 IMPORT CSV (IMPORTANT : AVANT {id})
    // =========================
    #[Route('/import', name: 'app_victime_import', methods: ['GET', 'POST'])]
    public function import(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {

            $file = $request->files->get('file');

            if (!$file) {
                $this->addFlash('danger', '❌ Aucun fichier sélectionné');
                return $this->redirectToRoute('app_victime_import');
            }

            if (($handle = fopen($file->getPathname(), 'r')) !== false) {

                $importSuccess = 0;
                $importError = 0;

                while (($data = fgetcsv($handle, 1000, ";")) !== false) {

                    if (count($data) < 11) {
                        $importError++;
                        continue;
                    }

                    $nom = trim($data[0]);
                    $postnom = trim($data[1]);
                    $prenom = trim($data[2]);
                    $avenueNom = trim($data[3]);
                    $numeroParcelle = trim($data[4]);
                    $homme = (int) $data[5];
                    $femme = (int) $data[6];
                    $enfant = (int) $data[7];
                    $docCode = trim($data[8]);
                    $telephone = trim($data[9]);
                    $description = trim($data[10]);

                    try {

                        // 🔎 Victime
                        $victime = $em->getRepository(Victime::class)->findOneBy([
                            'nom' => $nom,
                            'postnom' => $postnom,
                            'prenom' => $prenom,
                        ]);

                        if (!$victime) {
                            $victime = new Victime();
                            $victime->setNom($nom);
                            $victime->setPostnom($postnom);
                            $victime->setPrenom($prenom);
                            $victime->setTelephone($telephone);
                            $victime->setNumeroDossier(uniqid('DOS-'));
                            $victime->setUser($this->getUser());
                            $victime->setDateEnregistrement(new \DateTime());

                            $em->persist($victime);
                        }

                        // 🔎 Avenue
                        $avenue = $em->getRepository(Avenue::class)->findOneBy([
                            'nom' => $avenueNom
                        ]);

                        if (!$avenue) {
                            $importError++;
                            continue;
                        }

                        // 🔎 Parcelle
                        $parcelle = $em->getRepository(Parcelle::class)->findOneBy([
                            'victime' => $victime,
                            'avenue' => $avenue,
                            'numero' => $numeroParcelle,
                        ]);

                        if (!$parcelle) {
                            $parcelle = new Parcelle();
                            $parcelle->setVictime($victime);
                            $parcelle->setAvenue($avenue);
                            $parcelle->setNumero($numeroParcelle);
                            $parcelle->setHomme($homme);
                            $parcelle->setFemme($femme);
                            $parcelle->setEnfant($enfant);
                            $parcelle->setDescription($description);
                            $parcelle->setDatecreated(new \DateTime());

                            $em->persist($parcelle);
                        }

                        // 📎 Document
                        if (!empty($docCode)) {

                            $typeDoc = $em->getRepository(TypeDocument::class)->findOneBy([
                                'nom' => $docCode
                            ]);

                            if ($typeDoc) {

                                $existingDoc = $em->getRepository(Document::class)->findOneBy([
                                    'parcelle' => $parcelle,
                                    'typedocument' => $typeDoc
                                ]);

                                if (!$existingDoc) {
                                    $document = new Document();
                                    $document->setTypedocument($typeDoc);
                                    $document->setParcelle($parcelle);
                                    $document->setDateUpload(new \DateTime());

                                    $em->persist($document);
                                }
                            }
                        }

                        $importSuccess++;

                    } catch (\Exception $e) {
                        $importError++;
                    }
                }

                fclose($handle);
                $em->flush();

                $this->addFlash('success',
                    "✅ Import terminé : $importSuccess succès | $importError erreurs"
                );

                return $this->redirectToRoute('app_victime_index');
            }
        }

        return $this->render('victime/import.html.twig');
    }

    // =========================
    // 👁️ DETAIL
    // =========================
    #[Route('/{id}', name: 'app_victime_show', requirements: ['id' => '\d+'])]
    public function show(Victime $victime): Response
    {
        return $this->render('victime/show.html.twig', [
            'victime' => $victime,
        ]);
    }

    // =========================
    // ✏️ EDIT
    // =========================
   #[Route('/{id}/edit', name: 'app_victime_edit', requirements: ['id' => '\d+'])]
public function edit(Request $request, Victime $victime, EntityManagerInterface $em): Response
{
    $oldPhoto = $victime->getPhoto();

    $form = $this->createForm(VictimeType::class, $victime);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {

        if (!$form->isValid()) {
            dump($form->getErrors(true)); // 🔥 DEBUG
        }

        $file = $form->get('photo')->getData();

        if ($file) {

            // supprimer ancienne photo
            if ($oldPhoto) {
                $oldPath = $this->getParameter('uploads_directory') . '/' . $oldPhoto;
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $filename = uniqid() . '.' . $file->guessExtension();

            $file->move(
                $this->getParameter('uploads_directory'),
                $filename
            );

            $victime->setPhoto($filename);

        } else {
            $victime->setPhoto($oldPhoto);
        }

        $em->flush();

        $this->addFlash('success', '✏️ Victime modifiée avec succès');

        return $this->redirectToRoute('app_victime_index');
    }

    return $this->render('victime/edit.html.twig', [
        'form' => $form,
        'victime' => $victime,
    ]);
}

    // =========================
    // 🗑️ DELETE
    // =========================
    #[Route('/{id}', name: 'app_victime_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Victime $victime, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $victime->getId(), $request->request->get('_token'))) {

            $nom = $victime->getNom();

            $em->remove($victime);
            $em->flush();

            $this->addFlash('success', '🗑️ Victime ' . $nom . ' supprimée avec succès');
        }

        return $this->redirectToRoute('app_victime_index');
    }
}