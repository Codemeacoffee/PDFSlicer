<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Jobs\SlicePDF;
use App\ApiKeys;
use Imagick;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    function slicePDF(Request $request){
        $apiKey = htmlspecialchars($request['apiKey']);
        $returnUrl = htmlspecialchars($request['returnUrl']);
        $bookData = $request['bookData'];
        $pdf = $request['pdf'];
        
        $validatedKey = ApiKeys::where('key', $apiKey)->first();
        
        if(!$validatedKey || !$bookData || !$returnUrl || !$pdf) return abort('404');
        
        $time = strtotime(date('d/m/y h:i:s'));
        $random = $this->generateRandomString(20);
        $name = $random . $time . '.pdf';
        $dir = public_path().'/files/'.$name;
        
        file_put_contents($dir, fopen($pdf, 'r'));
        
        $sliceJob = new SlicePdf($dir, $name, $bookData, $returnUrl);
        dispatch($sliceJob);
    }
    
    function generateRandomString($length = 10)
    {
        return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    }
}
