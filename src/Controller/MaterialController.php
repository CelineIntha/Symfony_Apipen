<?php

namespace App\Controller;

use App\Entity\Material;
use App\Repository\MaterialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;

#[Route('/api')]
class MaterialController extends AbstractController
{
    /* Fonction qui permet de récupérer tous les matériaux */
    #[Route('/materials', name: 'app_materials', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Retourne tous les matériaux.',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Material::class, groups: ['material:read','pen:read']))
        )
    )]
    #[OA\Tag(name: 'Matériaux')]
    #[Security(name: 'Bearer')]
    public function index(MaterialRepository $materialRepository): JsonResponse
    {

        $materials = $materialRepository->findAll();

        return $this->json([
            'materials' => $materials,
        ], context: [
            'groups' => ['material:read', 'pen:read']
        ]);
    }
    #[Route('/material/{id}', name: 'app_material_get', methods: ['GET'])]
    #[OA\Tag(name: 'Matériaux')]
    public function get(Material $type): JsonResponse
    {
        return $this->json($type, context: [
            'groups' => ['material:read', 'pen:read']
        ]);
    }
    /* Fonction qui permet d'ajouter un matériel */
    #[Route('/materials', name: 'app_material_add', methods: ['POST'])]
    #[OA\Tag(name: 'Matériaux')]
    public function add(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        try {
            // On recupère les données du corps de la requête
            // Que l'on transforme ensuite en tableau associatif
            $data = json_decode($request->getContent(), true);

            // On traite les données pour créer un nouveau matériel
            $material = new Material();
            $material->setName($data['name']);

            $em->persist($material);
            $em->flush();

            return $this->json($material, context: [
                'groups' => ['material:read', 'pen:read'],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /* Fonction qui permet de mettre à jour le matériel*/
    #[Route('/material/{id}', name: 'app_material_update', methods: ['PUT', 'PATCH'])]
    #[OA\Tag(name: 'Matériaux')]
    // #[OA\Tag(name: 'Stylos')]
    public function update(
        Material $material,
        Request $request,
        EntityManagerInterface $em,
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            // On traite les données pour mettre à jour un matériel
            $material->setName($data['name']);

            $em->persist($material);
            $em->flush();

            return $this->json($material, context: [
                'groups' => ['material:read', 'pen:read'],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/material/{id}', name: 'app_material_delete', methods: ['DELETE'])]
    #[OA\Tag(name: 'Matériaux')]
    /* Fonction qui permet de supprimer un matériel */
    public function delete(Material $material, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($material);
        $em->flush();

        return $this->json([
            'code' => 200,
            'message' => 'Matériel supprimé'
        ]);
    }
}
