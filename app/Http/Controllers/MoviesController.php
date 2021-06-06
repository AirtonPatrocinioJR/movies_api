<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MoviesController extends Controller
{
    //
    var $movies_array = [];
    var $pagina = 1;
    var $tot_pagina = null;
    var $tot_movies = null;
    // 
    public function index2($title = null)
    {
        $title = urlencode($title);
        // dd($title);
        $response_data = $this->_getMovies($title);

        $list_moies_count = $this->_sortMovies($this->movies_array);

        $resposta_json = ["moviesByYear" => $list_moies_count, "total" => $this->tot_movies];


        return response()->json($resposta_json);
    }
    // 
    public function index(Request $request)
    {
        $title = urlencode($request->title);
        $response_data = $this->_getMovies($title);

        $list_moies_count = $this->_sortMovies($this->movies_array);

        $resposta_json = ["moviesByYear" => $list_moies_count, "total" => $this->tot_movies];


        return response()->json($resposta_json);
    }
    // 
    private function _getMovies($titulo)
    {
        if ($titulo == null || $titulo == '') {
            $api_url = "https://jsonmock.hackerrank.com/api/movies/search/?page=$this->pagina";
        } else {
            $api_url = "https://jsonmock.hackerrank.com/api/movies/search/?Title=$titulo&page=$this->pagina";
        }

        $opts = array(
            'http' =>
            array(
                'method'  => 'GET',
                'timeout' => 60
            )
        );
        $context = stream_context_create($opts);
        $json_data = file_get_contents($api_url, false, $context);
        // if ($this->pagina % 10 == 0) dump($this->pagina . ' - ' . $this->tot_pagina);
        // dd($json_data);

        $response_data = json_decode($json_data);
        $this->pagina = $response_data->page;
        $this->tot_pagina = $response_data->total_pages;
        $this->tot_movies = $response_data->total;

        $this->movies_array = array_merge($this->movies_array, $this->_mountArrayMovies($response_data));

        if ($response_data->page == $response_data->total_pages || $response_data->total_pages == 0) return $this->movies_array;

        $this->pagina++;
        return $this->_getMovies($titulo);
    }
    // 
    private function _mountArrayMovies($response_data)
    {
        $array = [];
        $total_pags = $response_data->total_pages;
        $init_pag = $response_data->page;
        foreach ($response_data->data as $data) {
            array_push($array, $data);
        }
        return $array;
    }
    private function _sortMovies($movies)
    {
        $list_movies = collect($movies);
        $count_resp = $list_movies->countBy('Year');
        $array = collect();
        $anos = $count_resp->keys();
        $count = 0;
        foreach ($count_resp as $key => $item) {
            $array->push(['year' => $anos[$count], 'movies' => $item]);
            $count++;
        }
        $resp = $array->sortBy('year');
        return $resp->values()->all();
    }
}
