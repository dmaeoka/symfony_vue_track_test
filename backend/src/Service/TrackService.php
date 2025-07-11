<?php
namespace App\Service;

use App\Entity\Track;
use Doctrine\ORM\EntityManagerInterface;

class TrackService
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function save(Track $track): void
    {
        $this->em->persist($track);
        $this->em->flush();
    }

    public function delete(Track $track): void
    {
        $this->em->remove($track);
        $this->em->flush();
    }
}
