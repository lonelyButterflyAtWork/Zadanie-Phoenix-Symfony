<?php

declare(strict_types=1);

namespace App\Controller\Phoenix;

use App\Api\Phoenix\User\UserClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\Phoenix\UserType;

class UserController extends AbstractController
{
    public function __construct(private readonly UserClientInterface $userClient) {}

    /**
     * @return Response
     */
    #[Route('/phoenix/users', name: 'phoenix_user_list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $filters = $request->query->all();
        try {
            $users = $this->userClient->listUsers($filters);
        } catch (\Throwable $e) {
            $this->addFlash('error', $e->getMessage());
            $users = [];
        }
        return $this->render('phoenix/user/list.html.twig', [
            'users' => $users,
            'filters' => $filters,
        ]);
    }

    /**
     * @return Response
     */
    #[Route('/phoenix/users/add', name: 'phoenix_user_add')]
    public function add(Request $request): Response
    {
        $form = $this->createForm(UserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    $created = $this->userClient->createUser($form->getData());
                    $this->addFlash('success', 'User added!');
                    if (isset($created['id'])) {
                        return $this->redirectToRoute('phoenix_user_show', ['id' => $created['id']]);
                    }
                    return $this->redirectToRoute('phoenix_user_list');
                } catch (\Throwable $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            } else {
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('danger', $error->getMessage());
                }
            }
        }

        return $this->render('phoenix/user/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @return Response
     */
    #[Route('/phoenix/users/{id}/edit', name: 'phoenix_user_edit')]
    public function edit(Request $request, int $id): Response
    {
        try {
            $user = $this->userClient->getUser($id);
        } catch (\Throwable $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('phoenix_user_list');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $updated = $this->userClient->updateUser($id, $form->getData());
                $this->addFlash('success', 'User updated!');
                if (isset($updated['id'])) {
                    return $this->redirectToRoute('phoenix_user_show', ['id' => $updated['id']]);
                }
                return $this->redirectToRoute('phoenix_user_list');
            } catch (\Throwable $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('phoenix/user/form.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @return Response
     */
    #[Route('/phoenix/users/{id}/delete', name: 'phoenix_user_delete', methods: ['POST'])]
    public function delete(int $id): Response
    {
        try {
            $this->userClient->deleteUser($id);
            $this->addFlash('success', 'User deleted!');
        } catch (\Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }
        return $this->redirectToRoute('phoenix_user_list');
    }

    /**
     * @return Response
     */
    #[Route('/phoenix/users/{id}', name: 'phoenix_user_show')]
    public function show(int $id): Response
    {
        try {
            $user = $this->userClient->getUser($id);
            if (isset($user['birthdate']) && $user['birthdate'] instanceof \DateTimeInterface) {
                $user['birthdate'] = $user['birthdate']->format('Y-m-d');
            }
        } catch (\Throwable $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('phoenix_user_list');
        }

        return $this->render('phoenix/user/show.html.twig', [
            'user' => $user,
        ]);
    }
}
