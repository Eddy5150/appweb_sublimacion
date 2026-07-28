<?php

namespace Database\Factories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    protected array $imagenes = [
        'Poleras estampadas' => ['/imagen/Poleras estampadas.jpg', '/imagen/Poleras estampadas2.jpg', '/imagen/Poleras estampadas4.jpg', '/imagen/Poleras estampadas5.jpg', '/imagen/sublimacion.jpg'],
        'Poleras sublimadas deportivas' => ['/imagen/sublimacion5.jpg', '/imagen/sublimacion8.jpg', '/imagen/sublimacion9.jpg'],
        'Shorts deportivos sublimados' => ['/imagen/Shorts deportivos1.jpg', '/imagen/Shorts deportivos2.jpg', '/imagen/Shorts deportivos3.jpg', '/imagen/Shorts deportivos4.jpg', '/imagen/sublimacion6.jpg', '/imagen/sublimacion7.jpg'],
    ];

    protected array $productos = [
        'Poleras estampadas' => [
            ['nombre' => 'Polera Estampada "Flow Tropical"', 'descripcion' => 'Estampado tropical full color en algodón premium 240 g/m². Diseño exclusivo con paleta de colores vibrantes. Cuello redondo reforzado, costuras doble aguja. Ideal para uso casual y eventos.', 'precio' => 79.90],
            ['nombre' => 'Polera Estampada "Urban Geometric"', 'descripcion' => 'Diseño geométrico moderno estampado en alta definición. Algodón peinado 100% preencogido. Corte regular fit con mangas cortas. Tintas ecológicas de larga duración que no se agrietan.', 'precio' => 69.90],
            ['nombre' => 'Polera Estampada "Neon Wave"', 'descripcion' => 'Estampado neón psicodélico sobre tela negro premium. Algodón ring spun 180 g/m². Cuello ribeteado con elastano. Diseño que cubre frente y espalda completa.', 'precio' => 89.90],
            ['nombre' => 'Polera Estampada "Abstract Art"', 'descripcion' => 'Diseño abstracto creado por artistas bolivianos. Estampado digital de alta resolución. Algodón orgánico suave al tacto. Corte moderno ligeramente ajustado. Disponible en tallas S-3XL.', 'precio' => 74.90],
            ['nombre' => 'Polera Estampada "Tribal Fusion"', 'descripcion' => 'Estampado tribal contemporáneo con acabado mate. Tela premium 200 g/m² con tratamiento anti-pilling. Costuras resistentes y cuello reforzado con cinta interior.', 'precio' => 84.90],
            ['nombre' => 'Polera Estampada "Retro Gaming"', 'descripcion' => 'Diseño retro pixel art estampado en full color. Algodón 100% con acabado vintage. Corte clásico holgado. Estampado resistente a lavados repetidos sin perder color.', 'precio' => 79.90],
            ['nombre' => 'Polera Estampada "Galaxy Space"', 'descripcion' => 'Estampado galaxia con detalles brillantes. Algodón premium 220 g/m² con acabado suave. Cuello redondo con cinta interior de refuerzo. Diseño único en tonos azules y púrpura.', 'precio' => 94.90],
            ['nombre' => 'Polera Estampada "Minimal Line"', 'descripcion' => 'Diseño minimalista con líneas finas estampadas en serigrafía. Algodón peinado 180 g/m². Corte moderno slim fit. Estampado ecológico libre de PVC.', 'precio' => 59.90],
            ['nombre' => 'Polera Estampada "Tropical Night"', 'descripcion' => 'Estampado tropical en tonos oscuros con detalles neón. Algodón premium con tratamiento suavizante. Costuras doble pespunte. Diseño exclusivo temporada 2026.', 'precio' => 89.90],
            ['nombre' => 'Polera Estampada "Aztec Spirit"', 'descripcion' => 'Diseño azteca contemporáneo estampado en alta definición. Tela 100% algodón 200 g/m². Corte regular con mangas cortas. Colores vibrantes resistentes a lavado.', 'precio' => 84.90],
        ],
        'Poleras sublimadas deportivas' => [
            ['nombre' => 'Polera Deportiva "Velocity"', 'descripcion' => 'Polera dry-fit de alto rendimiento con tecnología de sublimación total. Tejido 100% poliéster 150 g/m². Paneles de ventilación láser en axilas. Costuras planas antirozaduras. Protección UV 50+.', 'precio' => 119.90],
            ['nombre' => 'Polera Deportiva "Endurance"', 'descripcion' => 'Polera de competición con tejido ultraligero transpirable. Sublimación integral con colores vibrantes. Mangas raglán para libertad total de movimiento. Ideal para running y triatlón.', 'precio' => 129.90],
            ['nombre' => 'Polera Deportiva "Apex"', 'descripcion' => 'Polera deportiva profesional con tecnología antiolor. Tejido de secado rápido 140 g/m². Diseño ergonómico con malla en espalda. Costuras planas para máximo confort.', 'precio' => 139.90],
            ['nombre' => 'Polera Deportiva "Titan"', 'descripcion' => 'Polera de entrenamiento resistente con elastano 10% para elasticidad. Sublimación total con colores intensos. Cuello redondo con cinta interior. Ideal para crossfit y gimnasio.', 'precio' => 109.90],
            ['nombre' => 'Polera Deportiva "Fusion"', 'descripcion' => 'Polera deportiva con paneles de contraste sublimados. Tejido dry-fit 160 g/m² con protección UV. Bolsillo interno para llaves o gel. Costuras reforzadas para larga duración.', 'precio' => 124.90],
            ['nombre' => 'Polera Deportiva "Storm"', 'descripcion' => 'Polera de running con detalles reflectantes 3M. Tejido ultraliviano 130 g/m² con tratamiento antibacteriano. Sublimación frontal y espalda completa. Corte atlético.', 'precio' => 144.90],
            ['nombre' => 'Polera Deportiva "Carbon"', 'descripcion' => 'Polera de competición con fibra de carbono integrada. Peso pluma 120 g/m². Máxima transpirabilidad con paneles de malla. Diseño aerodinámico para máximo rendimiento.', 'precio' => 159.90],
            ['nombre' => 'Polera Deportiva "Nitro"', 'descripcion' => 'Polera deportiva acelerada con tejido de alto rendimiento. Sublimación total con acabado brillante. Cremallera escondida para ventilación. Ideal para ciclismo y running.', 'precio' => 149.90],
            ['nombre' => 'Polera Deportiva "Blaze"', 'descripcion' => 'Polera de fuego con diseño llamativo sublimado. Tejido dry-fit premium 155 g/m². Protección UV 50+ certificada. Mangas cortas con corte angular moderno.', 'precio' => 119.90],
            ['nombre' => 'Polera Deportiva "Quantum"', 'descripcion' => 'Polera tecnológica con tejido inteligente de regulación térmica. Sublimación integral. Costuras planas selladas. Ideal para deportes de alta intensidad y competición.', 'precio' => 169.90],
            ['nombre' => 'Polera Deportiva "Vertex"', 'descripcion' => 'Polera de alto rendimiento con paneles de compresión. Tejido elástico en 4 direcciones. Sublimación total con colores degradados. Bolsillo con cierre en espalda.', 'precio' => 154.90],
            ['nombre' => 'Polera Deportiva "Aero"', 'descripcion' => 'Polera aerodinámica de peso pluma 110 g/m². Tejido de secado ultra rápido. Diseño sin costuras laterales. Ideal para maratón y triatlón de larga distancia.', 'precio' => 139.90],
            ['nombre' => 'Polera Deportiva "Stratus"', 'descripcion' => 'Polera cloud comfort con tejido ultrasuave. Sublimación total con diseño degradado. Protección solar UPF 50+. Corte moderno con mangas raglán.', 'precio' => 134.90],
            ['nombre' => 'Polera Deportiva "Pulse"', 'descripcion' => 'Polera deportiva con diseño dinámico sublimado. Tejido dry-fit 145 g/m² con tratamiento antiolor. Detalles reflectantes para visibilidad nocturna. Ideal para running.', 'precio' => 129.90],
            ['nombre' => 'Polera Deportiva "Eclipse"', 'descripcion' => 'Polera con diseño eclipse en tonos oscuros. Tejido de compresión ligera 160 g/m². Sublimación total con detalles en alto contraste. Costuras planas ergonómicas.', 'precio' => 144.90],
            ['nombre' => 'Polera Deportiva "Horizon"', 'descripcion' => 'Polera deportiva con diseño de horizonte degradado. Tejido transpirable de secado rápido. Bolsillo interno con cierre. Ideal para entrenamiento al aire libre.', 'precio' => 119.90],
            ['nombre' => 'Polera Deportiva "Summit"', 'descripcion' => 'Polera de alta montaña con protección UV mejorada. Tejido resistente 170 g/m². Sublimación total con colores de alta visibilidad. Ideal para trail running y senderismo.', 'precio' => 159.90],
            ['nombre' => 'Polera Deportiva "Crusade"', 'descripcion' => 'Polera deportiva edición especial con diseño guerrero. Sublimación total premium. Tejido dry-fit 150 g/m² con elastano. Costuras doble pespunte para máxima resistencia.', 'precio' => 149.90],
        ],
        'Shorts deportivos sublimados' => [
            ['nombre' => 'Short Deportivo "Velocity"', 'descripcion' => 'Short de running con pretina elástica ajustable y cordón interior. Tejido sublimado 100% poliéster 160 g/m². Bolsillos laterales con cierre. Malla interior de sujeción. Costuras planas.', 'precio' => 109.90],
            ['nombre' => 'Short Deportivo "Endurance"', 'descripcion' => 'Short de competición ultraligero 140 g/m². Diseño aerodinámico con corte moderno. Cintura ancha con bolsillo interno para gel. Sublimación integral. Ideal para triatlón.', 'precio' => 124.90],
            ['nombre' => 'Short Deportivo "Apex"', 'descripcion' => 'Short deportivo profesional con tecnología antiolor. Tejido de secado rápido con elastano 8%. Bolsillo con cierre en espalda. Pretina elástica con cintas ajustables.', 'precio' => 119.90],
            ['nombre' => 'Short Deportivo "Titan"', 'descripcion' => 'Short de entrenamiento resistente con refuerzos en costuras. Tejido 170 g/m² con tratamiento antibacteriano. Bolsillos profundos con cierre. Ideal para crossfit y gimnasio.', 'precio' => 99.90],
            ['nombre' => 'Short Deportivo "Fusion"', 'descripcion' => 'Short deportivo con paneles de contraste sublimados. Pretina elástica ancha 5 cm. Bolsillo interno impermeable. Tejido dry-fit 155 g/m² con protección UV.', 'precio' => 114.90],
            ['nombre' => 'Short Deportivo "Storm"', 'descripcion' => 'Short de running con reflectores 3M integrados. Tejido ultraliviano 135 g/m². Bolsillo frontal con cremallera oculta. Corte moderno con aberturas laterales.', 'precio' => 129.90],
            ['nombre' => 'Short Deportivo "Carbon"', 'descripcion' => 'Short de competición con fibra de carbono. Peso mínimo 125 g/m². Diseño aerodinámico sin costuras laterales. Bolsillo interno sellado. Ideal para alto rendimiento.', 'precio' => 144.90],
            ['nombre' => 'Short Deportivo "Nitro"', 'descripcion' => 'Short deportivo acelerado con tejido de alto rendimiento. Sublimación total con acabado premium. Pretina elástica con silicona antideslizante. Bolsillo con cierre impermeable.', 'precio' => 134.90],
            ['nombre' => 'Short Deportivo "Blaze"', 'descripcion' => 'Short con diseño llamativo sublimado. Tejido dry-fit 150 g/m². Bolsillos laterales con malla. Cintura ajustable con cordón plano. Ideal para entrenamiento y uso diario.', 'precio' => 109.90],
            ['nombre' => 'Short Deportivo "Quantum"', 'descripcion' => 'Short tecnológico con tejido inteligente de regulación térmica. Cuatro bolsillos (2 laterales, 2 traseros). Sublimación integral. Costuras selladas de alta frecuencia.', 'precio' => 154.90],
            ['nombre' => 'Short Deportivo "Vertex"', 'descripcion' => 'Short de alto rendimiento con compresión muscular. Tejido elástico en 4 direcciones. Bolsillo con cierre en muslo. Sublimación total. Ideal para running y crossfit.', 'precio' => 139.90],
            ['nombre' => 'Short Deportivo "Aero"', 'descripcion' => 'Short aerodinámico de peso pluma 120 g/m². Diseño sin costuras internas. Bolsillo interno con cierre impermeable. Ideal para maratón y competición.', 'precio' => 129.90],
            ['nombre' => 'Short Deportivo "Stratus"', 'descripcion' => 'Short cloud comfort con tejido ultrasuave. Pretina elástica forrada. Bolsillos laterales con cremallera termoplástica. Sublimación total con diseño degradado.', 'precio' => 124.90],
            ['nombre' => 'Short Deportivo "Pulse"', 'descripcion' => 'Short deportivo con diseño dinámico sublimado. Tejido dry-fit 145 g/m². Bolsillo trasero con cierre. Reflectores integrados. Ideal para running nocturno.', 'precio' => 119.90],
            ['nombre' => 'Short Deportivo "Eclipse"', 'descripcion' => 'Short con diseño eclipse en tonos oscuros. Tejido 155 g/m² con elastano. Costuras planas ergonómicas. Bolsillo interno impermeable. Ideal para entrenamiento.', 'precio' => 134.90],
        ],
    ];

    public function definition(): array
    {
        $categoria = fake()->randomElement(array_keys($this->productos));
        $producto = fake()->randomElement($this->productos[$categoria]);
        $imagenes = $this->imagenes[$categoria];
        $imagen = $imagenes[array_rand($imagenes)];

        return [
            'nombre' => $producto['nombre'],
            'descripcion' => $producto['descripcion'],
            'precio' => $producto['precio'],
            'imagen' => $imagen,
            'categoria' => $categoria,
            'stock' => fake()->numberBetween(8, 65),
            'activo' => true,
        ];
    }

    public function withSpecificData(string $categoria, int $index): static
    {
        $producto = $this->productos[$categoria][$index] ?? $this->productos[$categoria][0];
        $imagenes = $this->imagenes[$categoria];
        $imagen = $imagenes[$index % count($imagenes)];

        return $this->state([
            'nombre' => $producto['nombre'],
            'descripcion' => $producto['descripcion'],
            'precio' => $producto['precio'],
            'imagen' => $imagen,
            'categoria' => $categoria,
            'stock' => fake()->numberBetween(8, 65),
            'activo' => true,
        ]);
    }
}
