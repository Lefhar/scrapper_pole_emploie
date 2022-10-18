<?php
ini_set('max_execution_time', 0);
require 'vendor/autoload.php';
use App\Scrapper;

$obj = new Scrapper();
echo $obj->getOffre("https://candidat.pole-emploi.fr/offres/recherche?motsCles=d%C3%A9veloppeur+web,d%C3%A9veloppeur+php&offresPartenaires=true&rayon=0&tri=0");


