# Graph Report - robasa-1  (2026-10-15)

## Corpus Check
- 107 files · ~37,332 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 20 file(s) not represented in the graph (top: (none) 16, .example 1, .xml 1)

## Summary
- 539 nodes · 856 edges · 63 communities (11 shown, 52 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS · INFERRED: 4 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `23d5132a`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- EstrategiaResultado
- composer.json
- User
- package.json
- ResultadoDimension
- ResultadoFinal
- database/database/migrations/2026_10_09_000003_create_preguntas_table.php
- Illuminate\Database\Schema\Blueprint
- UtilesEstrategia
- database/migrations/2026_10_09_000001_create_pruebas_table.php
- bootstrap/app.php
- public/app/Services/Psicometria/Estrategias/Combinacion.php
- AppServiceProvider
- Illuminate\Http\Request
- MotorPsicometrico
- 2026_10_20_000000_agregar_fk_postulacion_a_asignaciones_prueba. pendiente.php
- 0001_01_01_000001_create_cache_table.php
- public/app/Services/Psicometria/Estrategias/DimensionMayor.php
- MotorPsicometrico
- database/database/migrations/2026_10_09_000001_create_pruebas_table.php
- Illuminate\Database\Migrations\Migration
- database/database/migrations/2026_10_09_000004_create_opciones_table.php
- database/database/migrations/2026_10_09_000005_create_opcion_dimension_table.php
- Illuminate\Support\Facades\Schema
- database/database/migrations/2026_10_09_000007_create_rangos_interpretacion_table.php
- views.js
- database/database/migrations/2026_10_09_000009_create_respuestas_table.php
- database/database/migrations/2026_10_09_000010_create_resultados_dimension_table.php
- database/migrations/2026_10_09_000002_create_dimensiones_table.php
- database/migrations/2026_10_09_000003_create_preguntas_table.php
- README.md
- database/migrations/2026_10_09_000005_create_opcion_dimension_table.php
- database/migrations/2026_10_09_000006_create_perfiles_table.php
- database/migrations/2026_10_09_000007_create_rangos_interpretacion_table.php
- database/migrations/2026_10_09_000008_create_asignaciones_prueba_table.php
- database/migrations/2026_10_09_000011_create_resultados_prueba_table.php
- ResultadoDimension
- 0001_01_01_000002_create_jobs_table.php
- ResultadoFinal
- 2026_10_13_000001_add_dimension_padre_to_dimensiones_table.php
- 2026_10_20_000000_agregar_fk_postulacion_a_asignaciones_prueba.php
- database/database/migrations/2026_10_09_000011_create_resultados_prueba_table.php
- database/migrations/2026_10_09_000009_create_respuestas_table.php
- database/migrations/2026_10_09_000010_create_resultados_dimension_table.php
- 2026_10_13_000002_create_baremos_table.php
- PuntajeT
- CLAUDE.md
- login.blade.php
- contratista/index.blade.php
- show.blade.php
- postulante/index.blade.php

## God Nodes (most connected - your core abstractions)
1. `ResultadoFinal` - 30 edges
2. `ResultadoDimension` - 19 edges
3. `EstrategiaResultado` - 16 edges
4. `Combinacion` - 15 edges
5. `DimensionMayor` - 14 edges
6. `PuntajePorDimension` - 14 edges
7. `User` - 13 edges
8. `TotalCorrectas` - 12 edges
9. `UtilesEstrategia` - 12 edges
10. `EstrategiasResultadoTest` - 12 edges

## Surprising Connections (you probably didn't know these)
- `{closure#7}()` --references--> `ResultadoDimension`  [EXTRACTED]
  public/app/Services/Psicometria/MotorPsicometrico.php → app/Services/Psicometria/ResultadoDimension.php
- `Combinacion` --implements--> `EstrategiaResultado`  [EXTRACTED]
  public/app/Services/Psicometria/Estrategias/Combinacion.php → app/Services/Psicometria/Estrategias/EstrategiaResultado.php
- `DimensionMayor` --implements--> `EstrategiaResultado`  [EXTRACTED]
  public/app/Services/Psicometria/Estrategias/DimensionMayor.php → app/Services/Psicometria/Estrategias/EstrategiaResultado.php
- `PuntajePorDimension` --implements--> `EstrategiaResultado`  [EXTRACTED]
  public/app/Services/Psicometria/Estrategias/PuntajePorDimension.php → app/Services/Psicometria/Estrategias/EstrategiaResultado.php
- `PuntajeT` --implements--> `EstrategiaResultado`  [EXTRACTED]
  public/app/Services/Psicometria/Estrategias/PuntajeT.php → app/Services/Psicometria/Estrategias/EstrategiaResultado.php

## Import Cycles
- None detected.

## Communities (63 total, 52 thin omitted)

### Community 0 - "EstrategiaResultado"
Cohesion: 0.09
Nodes (10): Combinacion, DimensionMayor, EstrategiaResultado, PuntajePorDimension, TotalCorrectas, {closure#6}(), EstrategiasResultadoTest, {closure#7}() (+2 more)

### Community 1 - "composer.json"
Cohesion: 0.04
Nodes (47): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+39 more)

### Community 3 - "package.json"
Cohesion: 0.10
Nodes (19): devDependencies, axios, concurrently, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite, private (+11 more)

### Community 4 - "ResultadoDimension"
Cohesion: 0.12
Nodes (6): PuntajeT, {closure#1}(), ResultadoDimension, {closure#1}(), UtilesEstrategia, PuntajeTTest

### Community 7 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.14
Nodes (12): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#1}() (+4 more)

### Community 8 - "UtilesEstrategia"
Cohesion: 0.18
Nodes (3): UtilesEstrategia, PuntajePorDimension, TotalCorrectas

### Community 13 - "Illuminate\Http\Request"
Cohesion: 0.05
Nodes (12): AuthController, ContratistaController, Controller, PostulacionController, PostulanteController, EsContratista, AsignacionPrueba, Postulacion (+4 more)

### Community 15 - "MotorPsicometrico"
Cohesion: 0.16
Nodes (4): VerificarBigFive, MotorPsicometrico, BigFiveSeeder, DatabaseSeeder

### Community 27 - "views.js"
Cohesion: 0.14
Nodes (27): api, API_BASE, main, router(), clearWizard(), loadWizard(), saveWizard(), setVacanteId() (+19 more)

### Community 32 - "README.md"
Cohesion: 0.22
Nodes (8): About Laravel, Code of Conduct, Contributing, Laravel Sponsors, Learning Laravel, License, Premium Partners, Security Vulnerabilities

### Community 42 - "0001_01_01_000002_create_jobs_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

## Knowledge Gaps
- **7 isolated node(s):** `axios`, `concurrently`, `tailwindcss`, `layouts.app`, `layouts.app` (+2 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 227 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **52 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `Illuminate\Http\Request`, `MotorPsicometrico`?**
  _High betweenness centrality (0.204) - this node is a cross-community bridge._
- **What connects `axios`, `concurrently`, `tailwindcss` to the rest of the system?**
  _7 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `EstrategiaResultado` be split into smaller, more focused modules?**
  _Cohesion score 0.08672699849170437 - nodes in this community are weakly interconnected._
- **Why does `EstrategiaResultado` connect `EstrategiaResultado` to `ResultadoDimension`, `ResultadoFinal`, `UtilesEstrategia`, `public/app/Services/Psicometria/Estrategias/Combinacion.php`, `public/app/Services/Psicometria/Estrategias/DimensionMayor.php`, `PuntajeT`?**
  _High betweenness centrality (0.066) - this node is a cross-community bridge._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.041666666666666664 - nodes in this community are weakly interconnected._
- **Why does `Combinacion` connect `EstrategiaResultado` to `UtilesEstrategia`, `ResultadoFinal`, `app/Services/Psicometria/Estrategias/Combinacion.php`?**
  _High betweenness centrality (0.038) - this node is a cross-community bridge._
- **Should `User` be split into smaller, more focused modules?**
  _Cohesion score 0.11067193675889328 - nodes in this community are weakly interconnected._