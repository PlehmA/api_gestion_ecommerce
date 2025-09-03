<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class TestProductFiltering extends Command
{
    protected $signature = 'test:product-filtering {name?}';
    protected $description = 'Test product filtering functionality';

    public function handle()
    {
        $searchName = $this->argument('name') ?: 'smart';
        
        $this->info('🔍 Probando filtrado de productos...');
        
        // Mostrar todos los productos
        $this->line('📋 Productos en la base de datos:');
        Product::all(['id', 'name'])->each(function($product) {
            $this->line("   - ID: {$product->id} | Nombre: {$product->name}");
        });
        
        $this->newLine();
        
        // Probar filtro manual
        $this->line("🔎 Buscando productos que contengan: '{$searchName}'");
        $manualFilter = Product::where('name', 'LIKE', "%{$searchName}%")->get(['id', 'name']);
        
        if ($manualFilter->count() > 0) {
            $this->info('✅ Filtro manual encontró:');
            $manualFilter->each(function($product) {
                $this->line("   - ID: {$product->id} | Nombre: {$product->name}");
            });
        } else {
            $this->warn("❌ Filtro manual no encontró productos con '{$searchName}'");
        }
        
        $this->newLine();
        
        // Probar con QueryBuilder (simulando la API)
        $this->line('🔧 Probando con QueryBuilder...');
        
        // Simular request
        request()->merge(['filter' => ['name' => $searchName]]);
        
        $queryBuilderResults = QueryBuilder::for(Product::class)
            ->allowedFilters([
                AllowedFilter::partial('name'),
            ])
            ->get(['id', 'name']);
        
        if ($queryBuilderResults->count() > 0) {
            $this->info('✅ QueryBuilder encontró:');
            $queryBuilderResults->each(function($product) {
                $this->line("   - ID: {$product->id} | Nombre: {$product->name}");
            });
        } else {
            $this->warn("❌ QueryBuilder no encontró productos con '{$searchName}'");
        }
        
        // Mostrar la URL que se debería usar
        $this->newLine();
        $this->info("💡 URL correcta para filtrar: /api/products?filter[name]={$searchName}");
        $this->line("   También funciona: /api/products?filter[name]={$searchName}");
    }
}
