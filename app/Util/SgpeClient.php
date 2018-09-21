<?php

/**
 * Created by Fabricio Bizotto.
 * Date: 11/09/2018
 */

namespace App\Util;


use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;

class SgpeClient {

  private $base_url;
    // private $base_url = 'http://localhost:3000';
    // private $base_url = 'http://sgpedev.fraiburgo.ifc.edu.br';
  private $client;

  public function __construct($base_url = 'http://sgpe.fraiburgo.ifc.edu.br') {
    $this->base_url = $base_url;
    $this->cursos_sgpe = [];
    $this->ofertas = null;
    $this->sgpe_error = null;

    $this->client = new Client([
      'base_uri' => $this->base_url,
      'timeout'  => 20.0,
    ]);
  }

  public function buscarOfertas($ano, $semestre = null) {
    try {
      $resposta = $this->client->request('GET', "$this->base_url/offers/ptd_index.json?year=$ano");
      $resposta_json = json_decode($resposta->getBody());

      // foreach ($ofertas as $oferta) {
      //     if (!in_array($oferta->course->name, $cursos_sgpe)) {
      //         $cursos_sgpe[$oferta->course->id] = $oferta->course->name;
      //     }
      // }
      return $resposta_json;
    } catch (ConnectException $ex) {
      $sgpe_error = $ex->getMessage();
    } catch (Exception $ex) {
      $sgpe_error = $ex->getMessage();
    }
  }

  public function buscarOfertaPorId($idoffer) {
    try {
      $resposta = $this->client->request('GET', "$this->base_url/offers/$idoffer.json");
      $resposta_json = json_decode($resposta->getBody());


      return $resposta_json;
    } catch (ConnectException $ex) {
      $sgpe_error = $ex->getMessage();
    } catch (Exception $ex) {
      $sgpe_error = $ex->getMessage();
    }
  }

  public function getSgpeError() {
    return $this->sgpe_error;
  }

}
