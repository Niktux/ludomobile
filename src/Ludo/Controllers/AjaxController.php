<?php

namespace Ludo\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Ludo\Model\Games;

class AjaxController
{
    private
        $request,
        $model;

    public function __construct(Request $request, Games $model)
    {
        $this->request = $request;
        $this->model = $model;
    }

    public function playersLettersAction($letters)
    {
        return new JsonResponse(
            $this->model->fetchPlayersByFirstLetters(
                str_split($letters)
            )
        );
    }
}