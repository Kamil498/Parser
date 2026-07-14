<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\ProductBook;
use App\Enum\Shop;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AddBookController extends AbstractController
{
    #[Route('/books/add', name: 'book_add_form', methods: ['GET'])]
    public function form(): Response
    {
        return $this->render('book/add.html.twig');
    }

    #[Route(path:'/api/add-book', name: 'add_book', methods: ['POST'])]
    #[OA\Post(
        path: "/api/add-book",
        summary: "Dodaje nową książkę do monitorowania",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["url"],
                properties: [
                    new OA\Property(
                        property: "url",
                        type: "string",
                        example: "https://www.empik.com/przykladowa-ksiazka"
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Book added successfully"
            ),
            new OA\Response(
                response: 400,
                description: "Invalid request"
            )
        ]
    )]
    public function add(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

        if (!isset($data['url'])) {
            return $this->json([
                'success' => false,
                'message' => 'Pole url jest wymagane.'
            ], 400);
        }

        try {
            $shop = Shop::fromUrl($data['url']);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
        $exists=$em->getRepository(ProductBook::class)->findOneBy(['url' => $data['url']]);

        if($exists)
        {
            return $this->json([
                'success' => false,
                'message' => 'Book already exists'
            ], 400);
        }

        $now = new \DateTimeImmutable();



        $book = new ProductBook();

        $book->setUrl($data['url']);
        $book->setShop($shop->value);
        $book->setStatus('pending');


        $em->persist($book);
        $em->flush();

        return $this->json([
            'success' => true,
            'id' => $book->getId(),
            'shop' => $shop->value,
            'url' => $book->getUrl()
        ]);
    }


}
