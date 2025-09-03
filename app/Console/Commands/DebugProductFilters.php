<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;

class DebugProductFilters extends Command
{
    protected $signature = 'debug:product-filters';
    protected $description = 'Debug product filtering logic';

    public function handle()
    {
        $this->info('🔍 Debugeando lógica de filtros...');
        
        // Simular request con parámetro directo
        $request = new Request();
        $request->merge(['name' => 'smart']);
        
        $this->line('📨 Request original:');
        $this->line('   - name: ' . $request->input('name'));
        $this->line('   - filter: ' . json_encode($request->input('filter', [])));
        
        // Ejecutar la conversión
        $this->convertDirectParamsToFilters($request);
        
        $this->line('📨 Request después de conversión:');
        $this->line('   - name: ' . $request->input('name'));
        $this->line('   - filter: ' . json_encode($request->input('filter', [])));
        $this->line('   - filter[name]: ' . $request->input('filter.name'));
        
        // Verificar el query string
        $this->line('🔗 Query string: ' . $request->getQueryString());
        
        $this->info('✅ Debug completado');
    }
    
    /**
     * Convierte parámetros directos como ?name=valor a formato filter[campo]=valor
     * para mayor flexibilidad en la API
     */
    private function convertDirectParamsToFilters(Request $request)
    {
        $directParams = ['name', 'price', 'min_price', 'max_price', 'in_stock'];
        $currentFilters = $request->input('filter', []);
        
        foreach ($directParams as $param) {
            if ($request->has($param) && !isset($currentFilters[$param])) {
                $currentFilters[$param] = $request->input($param);
                $this->line("   ➕ Agregando filter[$param] = " . $request->input($param));
            }
        }
        
        if (!empty($currentFilters)) {
            $request->merge(['filter' => $currentFilters]);
            $this->line("   🔧 Merged filters: " . json_encode($currentFilters));
        }
    }
}
