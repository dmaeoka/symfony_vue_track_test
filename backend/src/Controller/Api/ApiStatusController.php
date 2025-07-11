<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiStatusController extends AbstractController
{
  #[Route('/', name: 'api_status', methods: ['GET'])]
  public function index(): JsonResponse
  {
    return $this->json(['message' => 'API is running']);
  }
}
