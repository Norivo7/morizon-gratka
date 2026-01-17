<?php

declare(strict_types=1);

namespace App\User\UI\Http\Controller;

use App\User\Application\Command\CreateUserCommand;
use App\User\Application\Command\DeleteUserCommand;
use App\User\Application\Command\UpdateUserCommand;
use App\User\Application\Query\ListUsersQuery;
use App\User\Infrastructure\Phoenix\PhoenixClient;
use App\User\UI\Http\Form\UserFilterType;
use App\User\UI\Http\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

final class UserController extends AbstractController
{
    #[Route('/users', name: 'user_index', methods: ['GET'])]
    public function index(Request $request, MessageBusInterface $bus): Response
    {
        $filterForm = $this->createForm(UserFilterType::class);
        $filterForm->handleRequest($request);

        $data = $filterForm->getData() ?? [];

        $birthdateFrom = null;
        if (isset($data['birthdate_from']) && $data['birthdate_from'] instanceof \DateTimeInterface) {
            $birthdateFrom = $data['birthdate_from']->format('Y-m-d');
        }

        $birthdateTo = null;
        if (isset($data['birthdate_to']) && $data['birthdate_to'] instanceof \DateTimeInterface) {
            $birthdateTo = $data['birthdate_to']->format('Y-m-d');
        }

        $query = new ListUsersQuery(
            firstName: isset($data['first_name']) ? (string) $data['first_name'] : null,
            lastName: isset($data['last_name']) ? (string) $data['last_name'] : null,
            gender: isset($data['gender']) ? (string) $data['gender'] : null,
            birthdateFrom: $birthdateFrom,
            birthdateTo: $birthdateTo,
            sortBy: $request->query->get('sort_by'),
            sortDir: $request->query->get('sort_dir'),
        );

        $envelope = $bus->dispatch($query);
        $handled = $envelope->last(HandledStamp::class);
        $users = $handled?->getResult() ?? [];

        return $this->render('user/index.html.twig', [
            'users' => $users,
            'filterForm' => $filterForm->createView(),
        ]);
    }

    #[Route('/users/new', name: 'user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, MessageBusInterface $bus): Response
    {
        $form = $this->createForm(UserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $bus->dispatch(new CreateUserCommand(
                firstName: $data['first_name'],
                lastName: $data['last_name'],
                gender: $data['gender'],
                birthdate: $data['birthdate']->format('Y-m-d'),
            ));

            $this->addFlash('success', 'User created.');

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/users/{id}/edit', name: 'user_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, PhoenixClient $client, MessageBusInterface $bus): Response
    {
        $user = $client->getUser($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $birthdate = null;
        if (isset($user['birthdate']) && is_string($user['birthdate']) && '' !== $user['birthdate']) {
            $birthdate = new \DateTimeImmutable($user['birthdate']);
        }

        $form = $this->createForm(UserType::class, [
            'first_name' => $user['first_name'] ?? '',
            'last_name' => $user['last_name'] ?? '',
            'gender' => $user['gender'] ?? '',
            'birthdate' => $birthdate,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $bus->dispatch(new UpdateUserCommand(
                id: $id,
                firstName: $data['first_name'],
                lastName: $data['last_name'],
                gender: $data['gender'],
                birthdate: $data['birthdate']->format('Y-m-d'),
            ));

            $this->addFlash('success', 'User updated.');

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'userId' => $id,
        ]);
    }

    #[Route('/users/{id}/delete', name: 'user_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(int $id, MessageBusInterface $bus): Response
    {
        $bus->dispatch(new DeleteUserCommand($id));

        $this->addFlash('success', 'User deleted.');

        return $this->redirectToRoute('user_index');
    }
}
