<?php

namespace MagmaticLabs\Obsidian\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\PassportToken;
use MagmaticLabs\Obsidian\Domain\Transformers\Transformer;

final class TokenController extends ResourceController
{
    /**
     * {@inheritdoc}
     */
    public function index(Request $request): Response
    {
        return new Response($this->collection(
            $request,
            PassportToken::query()->where('user_id', $this->getUser()->getKey()),
            new Transformer()
        ), 200);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request): Response
    {
        $data = $this->validate($request, [
            'data.id'                => 'not_present',
            'data.type'              => 'required|match:tokens',
            'data.attributes.name'   => 'required|string|max:255',
            'data.attributes.scopes' => 'sometimes|array|in:' . implode(',', Passport::scopeIds()),
        ])['data'];

        $scopes = $data['attributes']['scopes'] ?? [];

        $wrapper = $this->getUser()->createToken(trim($data['attributes']['name']), $scopes);

        $token = PassportToken::find($wrapper->token->id);
        $token->accessToken = (string) $wrapper->accessToken;

        $response = new Response($this->item(
            $token,
            new Transformer()
        ), 201);

        $response->withHeaders([
            'Location' => route('api.tokens.show', (string) $token->getKey()),
        ]);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function show(string $id): Response
    {
        if (empty($token = PassportToken::find($id))) {
            abort(404);
        }

        return new Response($this->item(
            $token,
            new Transformer()
        ), 200);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, string $id): Response
    {
        if (empty($token = $this->getUser()->tokens()->find($id))) {
            abort(404);
        }

        $data = $this->validate($request, [
            'data.id'                => "required|match:$id",
            'data.type'              => 'required|match:tokens',
            'data.attributes.name'   => 'sometimes|string|max:255',
            'data.attributes.scopes' => 'sometimes|array|in:' . implode(',', Passport::scopeIds()),
        ])['data'];

        $attributes = [];

        if (isset($data['attributes']['name'])) {
            $attributes['name'] = trim($data['attributes']['name']);
        }

        if (isset($data['attributes']['scopes'])) {
            $attributes['scopes'] = $data['attributes']['scopes'];
        }

        if (!empty($attributes)) {
            $token->update($attributes);
        }

        return new Response($this->item(
            PassportToken::find($id),
            new Transformer()
        ), 200);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(string $id): Response
    {
        if (empty($token = $this->getUser()->tokens()->find($id))) {
            abort(404);
        }

        try {
            $token->delete();
        } catch (\Throwable $ex) {
            return new Response('Failed to delete token', 500);
        }

        return new Response(null, 204);
    }
}
