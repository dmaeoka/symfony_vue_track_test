<?php
namespace App\Controller\Api;

use App\Entity\Track;
use App\Form\TrackType;
use App\Service\TrackService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/api/tracks')]
class TrackController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function list(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator, SerializerInterface $serializer): JsonResponse
    {
        $query = $em->getRepository(Track::class)
            ->createQueryBuilder('t')
            ->orderBy('t.id', 'DESC')
            ->getQuery();

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 5);

        $pagination = $paginator->paginate($query, $page, $limit);
        $data = [
            'items' => $pagination->getItems(),
            'pagination' => [
                'current_page' => $pagination->getCurrentPageNumber(),
                'total_items' => $pagination->getTotalItemCount(),
                'items_per_page' => $pagination->getItemNumberPerPage(),
                'total_pages' => ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage()),
            ]
        ];

        $json = $serializer->serialize($data, 'json');
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('', methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        TrackService $trackService
    ): JsonResponse {
        $track = new Track();
        $data = json_decode($request->getContent(), true);

        $form = $this->createForm(TrackType::class, $track);
        $form->submit($data);

        if (!$form->isValid()) {
            return $this->json(['errors' => $this->getFormErrors($form)], Response::HTTP_BAD_REQUEST);
        }

        $trackService->save($track);

        $json = $serializer->serialize($track, 'json');
        return new JsonResponse($json, Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        TrackService $trackService
    ): JsonResponse {
        $track = $em->getRepository(Track::class)->find($id);
        if (!$track) {
            return $this->json(['error' => 'Track not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(TrackType::class, $track);
        $form->submit($data);

        if (!$form->isValid()) {
            return $this->json(['errors' => $this->getFormErrors($form)], Response::HTTP_BAD_REQUEST);
        }

        $trackService->save($track);

        $json = $serializer->serialize($track, 'json');
        return new JsonResponse($json, Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(
        int $id,
        EntityManagerInterface $em,
        TrackService $trackService
    ): JsonResponse {
        $track = $em->getRepository(Track::class)->find($id);
        if (!$track) {
            return $this->json(['error' => 'Track not found'], Response::HTTP_NOT_FOUND);
        }

        $trackService->delete($track);

        return new JsonResponse(['message' => 'Track deleted successfully'], Response::HTTP_OK);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $track = $em->getRepository(Track::class)->find($id);
        if (!$track) {
            return $this->json(['error' => 'Track not found'], Response::HTTP_NOT_FOUND);
        }

        $json = $serializer->serialize($track, 'json');
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    private function getFormErrors($form): array
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $field = $error->getOrigin()?->getName() ?? 'form';
            $errors[$field] = $error->getMessage();
        }
        return $errors;
    }
}
