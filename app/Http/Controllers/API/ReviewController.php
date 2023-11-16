<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;


class ReviewController extends Controller
{
    public function getReviewByIdResto($id)
    {
        $review = Review::where('resto_id', $id)
            ->with('userCust', 'customer')
            ->get();
        return $this->handleResponse('Data Review', $review, Response::HTTP_OK);
    }

    public function getReviewByIdDriver($id)
    {
        $review = Review::where('driver_id', $id)
            ->with('userCust', 'customer')
            ->get();
        return $this->handleResponse('Data Review', $review, Response::HTTP_OK);
    }
}
