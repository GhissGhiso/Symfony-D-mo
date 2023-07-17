<?php 

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

#[Route('/posts', name: 'post_')]
final class PostController extends AbstractController
{
    #[Route('', name: 'list', methods: [Request::METHOD_GET])]
    public function list(PostRepository $postRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        $posts = $postRepository->getPaginatedPosts($page);

        $total = $posts->count();

        return $this->render('post/list.html.twig',[
            'posts' => $posts,
            'pagination' => [
                'page' => $page,
                'pages' => ceil($total / 18),
                'range' => range(max(1, $page - 3), min(ceil($total / 18), ceil($total / 18) + 3))
            ]
        ]);
    }

    #[Route('/create', name: 'create', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, EntityManagerInterface $entityManager, string $uploadDir): Response
    {
        $post = new Post;

        // /** @var User $user */
        // $user = $this->getUser();

        $post->setUser($this->getUser());

        $form = $this->createForm(
            PostType::class,
            $post,
            ['validation_groups' => ['Default' => 'create']]
        )->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setPublishedAt(new DateTimeImmutable());
            $post->setImage(sprintf(
                '%s.%s',
                Uuid::v4(),
                $post->getImageFile()->getClientOriginalExtension()
            ));
            $post->getImageFile()->move($uploadDir, $post->getImage());
            $entityManager->persist($post);
            $entityManager->flush();
            return $this->redirectToRoute('post_read', [ 'id' => $post->getId()]);
        }

        return $this->render('post/create.html.twig',[ 'form' => $form->createView() ]);
    }

    #[Route('/{id<\d+>}/read', name: 'read', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function read(Post $post): Response
    {
        return $this->render('post/read.html.twig',compact('post'));
    }

    #[Route('/{id<\d+>}/update', name: 'update', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    #[IsGranted('edit', subject: 'post')]
    public function update(Post $post, Request $request, EntityManagerInterface $entityManager, string $uploadDir): Response
    {
        $form = $this->createForm(PostType::class, $post)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($post->getImageFile() !== null) {
                $post->setImage(sprintf(
                    '%s.%s',
                    Uuid::v4(),
                    $post->getImageFile()->getClientOriginalExtension()
                ));
                $post->getImageFile()->move($uploadDir, $post->getImage());
            }
            $entityManager->flush();
            return $this->redirectToRoute('post_read', [ 'id' => $post->getId()]);
        }

        return $this->render('post/update.html.twig',[ 'form' => $form->createView() ]);
    }

    #[Route('/{id<\d+>}/delete', name: 'delete', methods: [Request::METHOD_POST])]
    #[IsGranted('edit', subject: 'post')]
    public function delete(Post $post, EntityManagerInterface $entityManager): RedirectResponse
    {
        $entityManager->remove($post);
        $entityManager->flush();
        return $this->redirectToRoute('post_list');
    }
}
