<?php

namespace MagmaticLabs\Obsidian\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use MagmaticLabs\Obsidian\Domain\Transformers\UserTransformer;

final class UserController extends ResourceController
{
    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request): Response
    {
        $this->authorize('index', User::class);

        return new Response($this->collection(
            $request,
            User::query(),
            new UserTransformer()
        ), 200);
    }

    /**
     * {@inheritdoc}
     */
    public function create(Request $request): Response
    {
        return $this->unavailable($request);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(string $id): Response
    {
        $this->authorize('show', $user = User::findOrFail($id));

        return new Response($this->item(
            $user,
            new UserTransformer()
        ), 200);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Request $request, string $id): Response
    {
        User::findOrFail($id);

        return $this->unavailable($request);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(string $id): Response
    {
        User::findOrFail($id);

        return $this->unavailable(request());
    }
}
