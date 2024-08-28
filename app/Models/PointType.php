<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

enum PointType: string{
    case BIWAK = 'biwak';
    case JAZ = 'jaz';
    case MOST = 'most';
    case PRZENOSKA = 'przenoska';
    case SKLEP = 'sklep';
    case STANICA = 'stanica';
    case UJSCIE = 'ujście';
    case UWAGA = 'uwaga';
    case WYPLYW = 'wypływ';
    case WYPOZYCZALNIA = 'wypożyczalnia';
    case NIEBEZPIECZENSTWO = 'niebezpieczeństwo';
    case LEKARZ = 'lekarz';
    case BAR = 'bar';
    case SLUZA = 'śluza';
}
