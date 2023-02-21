<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Imagick;
use Exception;


class SlicePDF implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $pdf;
    protected $name;
    protected $bookData;
    protected $returnUrl;
    public $timeout = 1800;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($pdf, $name, $bookData, $returnUrl)
    {
        $this->pdf = $pdf;
        $this->name = $name;
        $this->bookData = $bookData;
        $this->returnUrl = $returnUrl;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $generated = bin2hex(random_bytes(mt_rand(10, 25)));
        
        
        try{
            $i = 0;
            while(true){
                $imagick = new Imagick();
                $imagick->setResolution(300, 300);
                $imagick->readImage($this->pdf.'['.$i.']');
                $imagick->writeImages('/home/zb3f46kb/pdfslicer.com/images/'.$generated.'-'.$i.'.jpg', false);
                $i++;
            }
        }catch(Exception $e){}
       
        $i = 0;
        $files = ['bookData' => $this->bookData, 'pdf' => 'http://pdfslicer.gesforcan.com/files/'.$this->name];

        while(file_exists('/home/zb3f46kb/pdfslicer.com/images/'.$generated.'-'.$i.'.jpg')){
            $files['images-'.$i] = 'http://pdfslicer.gesforcan.com/images/'.$generated.'-'.$i.'.jpg';
            $i++;
        }

        $request = curl_init($this->returnUrl);
        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt($request, CURLOPT_POSTFIELDS, $files);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_exec($request);
        curl_close($request);
        
        if(File::exists($this->pdf)){
            File::delete($this->pdf);
        }
        
        $i = 0;
        
        while(file_exists('/home/zb3f46kb/pdfslicer.com/images/'.$generated.'-'.$i.'.jpg')){
            File::delete('/home/zb3f46kb/pdfslicer.com/images/'.$generated.'-'.$i.'.jpg');
            $i++;
        }
    }
}
