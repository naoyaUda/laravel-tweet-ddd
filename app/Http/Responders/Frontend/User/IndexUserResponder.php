<?php

namespace App\Http\Responders\Frontend\User;

use App\Http\Responders\Base\Responder;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class IndexUserResponder extends Responder implements Responsable
{
    private Collection $users;

    /** @var int amount of items shown in per pages */
    private int $perPage = 30;

    /**
     * Create response.
     * Using LengthAwarePagination to paginate.
     *
     * HACK: now, we get ALL users for pagination, should change fetching only targeted users for efficiency.
     *
     * @param [type] $request
     * @return Response
     */
    public function toResponse($request): Response
    {
        $page = (int) $request->query('page');

        $paginatedUsers = app()->makeWith(LengthAwarePaginator::class, [
            'items'   => $this->users->forPage($page, $this->perPage),
            'total'   => $this->users->count(),
            'perPage' => $this->perPage,
        ]);

        return new Response(
            $this->view->make('frontend.user.index', ['users' => $paginatedUsers->withPath('/users')])
        );
    }

    /**
     * Set UserDTO
     *
     * @param Collection $users
     * @return self
     */
    public function setUsers(Collection $users): self
    {
        $this->users = $users;
        return clone $this;
    }
}
