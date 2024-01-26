<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Repository\BrandRepository;
use OpenApi\Attributes as OA;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api')]
class BrandController extends AbstractController
{
    #[Route('/brands', name: 'app_brands', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Retourne toutes les marques.',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Brand::class, groups: ['brand:read','pen:read']))
        )
    )]
    #[OA\Tag(name: 'Marques')]
    #[Security(name: 'Bearer')]
    /* Fonction qui permet de récupérer toutes les marques */
    public function index(BrandRepository $brandRepository): JsonResponse
    {

        $brands = $brandRepository->findAll();

        return $this->json([
            'brands' => $brands,
        ], context: [
            'groups' => ['brand:read', 'pen:read']
        ]);
    }

    /* Fonction qui permet d'obtenir une une marque */
    #[Route('/brand/{id}', name: 'app_brand_get', methods: ['GET'])]
    #[OA\Tag(name: 'Marques')]
    public function get(Brand $brand): JsonResponse
    {
        return $this->json($brand, context: [
            'groups' => ['brand:read', 'pen:read']
        ]);
    }


    #[Route('/brands', name: 'app_brand_add', methods: ['POST'])]
    #[OA\Tag(name: 'Marques')]
    /* Fonction qui permet d'ajouter une marque */
    public function add(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        try {
            // On recupère les données du corps de la requête
            // Que l'on transforme ensuite en tableau associatif
            $data = json_decode($request->getContent(), true);

            // On traite les données pour créer une nouvelle marque
            $brand = new Brand();
            $brand->setName($data['name']);

            $em->persist($brand);
            $em->flush();

            return $this->json($brand, context: [
                'groups' => ['brand:read', 'pen:read'],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/brand/{id}', name: 'app_brand_update', methods: ['PUT', 'PATCH'])]
    #[OA\Tag(name: 'Marques')]
    /* Fonction qui permet de mettre à jour la marque */
    public function update(
        Brand $brand,
        Request $request,
        EntityManagerInterface $em,
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            // On traite les données pour mettre à jour la marque
            $brand->setName($data['name']);

            $em->persist($brand);
            $em->flush();

            return $this->json($brand, context: [
                'groups' => ['brand:read', 'pen:read'],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/brand/{id}', name: 'app_brand_delete', methods: ['DELETE'])]
    #[OA\Tag(name: 'Marques')]
    /* Fonction qui permet de supprimer la marque */
    public function delete(Brand $brand, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($brand);
        $em->flush();

        return $this->json([
            'code' => 200,
            'message' => 'Marque supprimé'
        ]);
    }
}