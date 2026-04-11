<?php

namespace App\EventListener;

use App\Entity\Victime;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\DBAL\LockMode;

class VictimeListener
{
    public function prePersist(Victime $victime, LifecycleEventArgs $args): void
    {
        $em = $args->getEntityManager();

        $prefix = 'VLDK';
        $year = date('Y');

        // Vérifier si le numerodossier n'existe pas déjà
        if ($victime->getNumerodossier() !== null) {
            return;
        }

        // Récupérer le dernier numéro pour l'année courante avec verrouillage pour éviter doublons
        $conn = $em->getConnection();
        $sql = "SELECT numerodossier FROM victime 
                WHERE numerodossier LIKE :pattern 
                ORDER BY numerodossier DESC 
                LIMIT 1 
                FOR UPDATE"; // verrou SQL
        $stmt = $conn->prepare($sql);
        $stmt->execute(['pattern' => $prefix . $year . '%']);
        $last = $stmt->fetchOne();

        if ($last) {
            // Extraire le compteur (4 derniers chiffres)
            $lastNumber = (int) substr($last, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        // Créer le numéro complet : VLDK20260001
        $numerodossier = $prefix . $year . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        $victime->setNumerodossier($numerodossier);
    }
}
    