<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('records', function (Blueprint $table) {
            $table->string('survey_type')->default('wellness');
            $table->enum('status', [
                'created', 
                'processing',   
                'completed',    
                'failed'        
            ])->default('created');
            $table->string('audio_url')->nullable();
            $table->string('audio_format')->nullable();
            $table->json('upload_urls')->nullable();

            // Datos de respuesta enviados (JSON) - para END
            $table->json('response_data')->nullable();
            
            // Resultados del análisis (JSON)
            $table->json('scores')->nullable();

            // Metadata adicional
            $table->text('error_message')->nullable(); // Si status = 'failed'
            $table->timestamp('started_at')->nullable(); // Cuando se inició
            $table->timestamp('uploaded_at')->nullable(); // Cuando se subió el audio
            $table->timestamp('processed_at')->nullable(); // Cuando se completó

            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('records', function (Blueprint $table) {
            $table->dropColumn([
                'survey_type',
                'status',
                'duration',
                'audio_url',
                'audio_format',
                'upload_urls',
                'response_data',
                'scores',
                'error_message',
                'started_at',
                'uploaded_at',
                'processed_at'
            ]);
            $table->dropIndex('records_status_index');
            $table->dropIndex('records_created_at_index');
        });
    }
};
