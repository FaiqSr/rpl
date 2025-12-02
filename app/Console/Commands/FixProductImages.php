<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductImage;
use Illuminate\Support\Str;

class FixProductImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:fix-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix product images that use invalid placeholder URLs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for invalid product image URLs...');
        
        // Find all product images with via.placeholder.com URLs
        $invalidImages = ProductImage::where('url', 'like', '%via.placeholder.com%')->get();
        
        if ($invalidImages->isEmpty()) {
            $this->info('No invalid image URLs found.');
            return 0;
        }
        
        $this->info("Found {$invalidImages->count()} invalid image URLs.");
        
        $bar = $this->output->createProgressBar($invalidImages->count());
        $bar->start();
        
        foreach ($invalidImages as $image) {
            // Generate SVG placeholder as data URI
            $productName = $image->name ?? 'Product';
            $productName = str_replace(' Image', '', $productName);
            
            $svgPlaceholder = 'data:image/svg+xml;base64,' . base64_encode(
                '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">' .
                '<rect width="400" height="400" fill="#f3f4f6"/>' .
                '<text x="50%" y="50%" font-family="Arial, sans-serif" font-size="18" fill="#6b7280" text-anchor="middle" dy=".3em">' . 
                htmlspecialchars($productName) . 
                '</text>' .
                '</svg>'
            );
            
            $image->update(['url' => $svgPlaceholder]);
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('All invalid image URLs have been fixed!');
        
        return 0;
    }
}

