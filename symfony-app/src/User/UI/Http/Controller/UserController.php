<?php

declare(strict_types=1);

namespace App\User\UI\Http\Controller;

use App\User\Infrastructure\Phoenix\PhoenixClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class UserController extends AbstractController
{
    private const array ALLOWED_QUERY_KEYS = [
        'first_name', 'last_name', 'gender',
        'birthdate_from', 'birthdate_to',
        'sort_by', 'sort_dir',
    ];

    #[Route('/users', name: 'user_index', methods: ['GET'])]
    public function index(Request $request, PhoenixClient $client): Response
    {
        $query = $this->extractAllowedQueryParams($request);

        $users = $client->listUsers($query);

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    private function extractAllowedQueryParams(Request $request): array
    {
        $query = [];
        foreach (self::ALLOWED_QUERY_KEYS as $key) {
            $value = $request->query->get($key);
            if ($value !== null && $value !== '') {
                $query[$key] = $value;
            }
        }

        return $query;
    }
}