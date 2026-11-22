if (! Schema::hasColumn('products', 'sold_count')) {
    Schema::table('products', function (Blueprint $table) {
        $table->unsignedInteger('sold_count')->default(0)->after('stock');
    });
}

if (! Schema::hasColumn('products', 'total_earned')) {
    Schema::table('products', function (Blueprint $table) {
        $table->decimal('total_earned', 12, 2)->default(0)->after('sold_count');
    });
}