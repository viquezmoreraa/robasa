# Graph Report - robasa-1  (2026-10-14)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 379 nodes · 573 edges · 50 communities (8 shown, 42 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `1c85246b`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Combinacion
- composer.json
- User
- package.json
- ResultadoDimension
- ResultadoFinal
- Illuminate\Database\Migrations\Migration
- Illuminate\Database\Schema\Blueprint
- UtilesEstrategia
- Illuminate\Support\Facades\Schema
- bootstrap/app.php
- public/app/Services/Psicometria/Estrategias/Combinacion.php
- AppServiceProvider
- TestCase
- MotorPsicometrico
- 2026_10_20_000000_agregar_fk_postulacion_a_asignaciones_prueba. pendiente.php
- 0001_01_01_000001_create_cache_table.php
- public/app/Services/Psicometria/Estrategias/DimensionMayor.php
- MotorPsicometrico
- database/database/migrations/2026_10_09_000001_create_pruebas_table.php
- database/database/migrations/2026_10_09_000002_create_dimensiones_table.php
- database/database/migrations/2026_10_09_000004_create_opciones_table.php
- database/database/migrations/2026_10_09_000005_create_opcion_dimension_table.php
- database/database/migrations/2026_10_09_000006_create_perfiles_table.php
- database/database/migrations/2026_10_09_000007_create_rangos_interpretacion_table.php
- database/database/migrations/2026_10_09_000008_create_asignaciones_prueba_table.php
- database/database/migrations/2026_10_09_000009_create_respuestas_table.php
- database/database/migrations/2026_10_09_000010_create_resultados_dimension_table.php
- database/migrations/2026_10_09_000002_create_dimensiones_table.php
- database/migrations/2026_10_09_000003_create_preguntas_table.php
- database/migrations/2026_10_09_000004_create_opciones_table.php
- database/migrations/2026_10_09_000005_create_opcion_dimension_table.php
- database/migrations/2026_10_09_000006_create_perfiles_table.php
- database/migrations/2026_10_09_000007_create_rangos_interpretacion_table.php
- database/migrations/2026_10_09_000008_create_asignaciones_prueba_table.php
- database/migrations/2026_10_09_000011_create_resultados_prueba_table.php
- ResultadoDimension
- ResultadoFinal
- Controller.php

## God Nodes (most connected - your core abstractions)
1. `ResultadoFinal` - 26 edges
2. `Combinacion` - 15 edges
3. `ResultadoDimension` - 15 edges
4. `DimensionMayor` - 14 edges
5. `EstrategiaResultado` - 14 edges
6. `PuntajePorDimension` - 14 edges
7. `TotalCorrectas` - 12 edges
8. `EstrategiasResultadoTest` - 12 edges
9. `EstrategiasResultadoTest` - 12 edges
10. `UtilesEstrategia` - 12 edges

## Surprising Connections (you probably didn't know these)
- `{closure#6}()` --references--> `ResultadoDimension`  [EXTRACTED]
  public/app/Services/Psicometria/MotorPsicometrico.php → app/Services/Psicometria/ResultadoDimension.php
- `Combinacion` --implements--> `EstrategiaResultado`  [EXTRACTED]
  public/app/Services/Psicometria/Estrategias/Combinacion.php → app/Services/Psicometria/Estrategias/EstrategiaResultado.php
- `DimensionMayor` --implements--> `EstrategiaResultado`  [EXTRACTED]
  public/app/Services/Psicometria/Estrategias/DimensionMayor.php → app/Services/Psicometria/Estrategias/EstrategiaResultado.php
- `PuntajePorDimension` --implements--> `EstrategiaResultado`  [EXTRACTED]
  public/app/Services/Psicometria/Estrategias/PuntajePorDimension.php → app/Services/Psicometria/Estrategias/EstrategiaResultado.php
- `TotalCorrectas` --implements--> `EstrategiaResultado`  [EXTRACTED]
  public/app/Services/Psicometria/Estrategias/TotalCorrectas.php → app/Services/Psicometria/Estrategias/EstrategiaResultado.php

## Import Cycles
- None detected.

## Communities (50 total, 42 thin omitted)

### Community 0 - "Combinacion"
Cohesion: 0.09
Nodes (9): Combinacion, DimensionMayor, EstrategiaResultado, PuntajePorDimension, TotalCorrectas, EstrategiasResultadoTest, {closure#6}(), ExampleTest (+1 more)

### Community 1 - "composer.json"
Cohesion: 0.04
Nodes (47): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+39 more)

### Community 2 - "User"
Cohesion: 0.11
Nodes (3): User, UserFactory, DatabaseSeeder

### Community 3 - "package.json"
Cohesion: 0.09
Nodes (19): devDependencies, axios, concurrently, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite, private (+11 more)

### Community 4 - "ResultadoDimension"
Cohesion: 0.16
Nodes (5): {closure#1}(), {closure#6}(), ResultadoDimension, {closure#1}(), UtilesEstrategia

### Community 6 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.15
Nodes (3): {closure#1}(), {closure#1}(), {closure#1}()

### Community 7 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.23
Nodes (6): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#3}()

### Community 8 - "UtilesEstrategia"
Cohesion: 0.18
Nodes (3): UtilesEstrategia, PuntajePorDimension, TotalCorrectas

## Knowledge Gaps
- **2 isolated node(s):** `concurrently`, `tailwindcss`
  These have ≤1 connection - possible missing edges. (Counts symbols only; 176 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **42 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `ResultadoFinal` connect `ResultadoFinal` to `ResultadoDimension`, `app/Services/Psicometria/Estrategias/DimensionMayor.php`, `UtilesEstrategia`, `public/app/Services/Psicometria/Estrategias/Combinacion.php`, `app/Services/Psicometria/Estrategias/Combinacion.php`, `MotorPsicometrico`, `public/app/Services/Psicometria/Estrategias/DimensionMayor.php`, `MotorPsicometrico`?**
  _High betweenness centrality (0.028) - this node is a cross-community bridge._
- **What connects `concurrently`, `tailwindcss` to the rest of the system?**
  _2 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Combinacion` be split into smaller, more focused modules?**
  _Cohesion score 0.09306122448979592 - nodes in this community are weakly interconnected._
- **Why does `ResultadoDimension` connect `ResultadoDimension` to `Combinacion`, `ResultadoFinal`?**
  _High betweenness centrality (0.018) - this node is a cross-community bridge._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.041666666666666664 - nodes in this community are weakly interconnected._
- **Why does `Combinacion` connect `Combinacion` to `UtilesEstrategia`, `ResultadoFinal`, `app/Services/Psicometria/Estrategias/Combinacion.php`?**
  _High betweenness centrality (0.015) - this node is a cross-community bridge._
- **Should `User` be split into smaller, more focused modules?**
  _Cohesion score 0.10666666666666667 - nodes in this community are weakly interconnected._