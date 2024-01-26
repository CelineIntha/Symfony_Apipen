<?php

namespace App\Controller;

use App\Entity\Color;
use App\Repository\ColorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/api')]
class ColorController extends AbstractController
{
    #[Route('/colors', name: 'app_colors', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Retourne toutes les marques.',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Color::class, groups: ['color:read','pen:read']))
        )
    )]
    #[OA\Tag(name: 'Couleurs')]
    #[Security(name: 'Bearer')]
    /* Fonction qui permet de récupérer toutes les couleurs */
    public function index(ColorRepository $colorRepository): JsonResponse
    {

        $colors = $colorRepository->findAll();

        return $this->json([
            'colors' => $colors,
        ], context: [
            'groups' => ['color:read', 'pen:read']
        ]);
    }

    #[Route('/color/{id}', name: 'app_color_get', methods: ['GET'])]
    #[OA\Tag(name: 'Couleurs')]
    public function get(Color $type): JsonResponse
    {
        return $this->json($type, context: [
            'groups' => ['color:read', 'pen:read']
        ]);
    }

    /* Fonction qui permet d'ajouter une nouvelle couleur */
    #[Route('/colors', name: 'app_color_add', methods: ['POST'])]
    #[OA\Tag(name: 'Couleurs')]
    /* Fonction qui permet d'ajouter une couleur */
    public function add(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        try {
            // On recupère les données du corps de la requête
            // Que l'on transforme ensuite en tableau associatif
            $data = json_decode($request->getContent(), true);

            // On traite les données pour créer un nouveau Stylo
            $color = new Color();
            $color->setName($data['name']);

            $em->persist($color);
            $em->flush();

            return $this->json($color, context: [
                'groups' => ['color:read', 'pen:read'],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    /* Fonction qui permet de mettre à jour la couleur*/
    #[Route('/color/{id}', name: 'app_color_update', methods: ['PUT', 'PATCH'])]
    #[OA\Tag(name: 'Couleurs')]
    // #[OA\Tag(name: 'Stylos')]
    public function update(
        Color $color,
        Request $request,
        EntityManagerInterface $em,
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            // On traite les données pour mettre à jour une couleur
            $color->setName($data['name']);

            $em->persist($color);
            $em->flush();

            return $this->json($color, context: [
                'groups' => ['color:read', 'pen:read'],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    #[Route('/color/{id}', name: 'app_color_delete', methods: ['DELETE'])]
    #[OA\Tag(name: 'Couleurs')]
    /* Fonction qui permet de supprimer une couleur */
    public function delete(Color $color, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($color);
        $em->flush();

        return $this->json([
            'code' => 200,
            'message' => 'Couleur supprimée'
        ]);
    }
}
