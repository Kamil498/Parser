<?php

namespace App\Controller;

use App\Entity\Book;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class AddBookController
{

    #[Route(path:"api/add-book", name:"add_book", methods:['POST'])]
    public function add(EntityManagerInterface $em, Request $request){

        $data=json_decode($request->getContent(), true);

        if(!isset($data['url']) || !isset($data['shop']) || !isset($data['is_active']) || !isset($data['created_at']) || !isset($data['updated_at'])){
            throw new BadRequestHttpException();
        }

        $book = new Book();
        $book->setUrl($data['url']);
        $book->setShop($data['shop']);
        $book->setIsActive($data['is_active']);
        $book->setCreatedAt($data['created_at']);
        $book->setUpdatedAt($data['updated_at']);

        try{
            $em->persist($book);
            $em->flush();
        }
        catch (UniqueConstraintViolationException){
            throw new BadRequestHttpException();
        }

        return $this->json([
            'success' => true,
            'book' => $book
        ]);

    }


}
