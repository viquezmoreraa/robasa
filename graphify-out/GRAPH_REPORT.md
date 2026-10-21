# Graph Report - robasa-1  (2026-10-21)

## Corpus Check
- 136 files · ~65,390 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 20 file(s) not represented in the graph (top: (none) 16, .example 1, .xml 1)

## Summary
- 666 nodes · 1094 edges · 75 communities (15 shown, 60 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 7 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `404f9f47`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- EstrategiasResultadoTest
- composer.json
- MotorPsicometrico
- package.json
- .estrategiaPara
- ResultadoFinal
- AutenticacionTest
- Illuminate\Database\Schema\Blueprint
- ResultadoDimension
- database/migrations/2026_10_09_000001_create_pruebas_table.php
- bootstrap/app.php
- public/app/Services/Psicometria/Estrategias/Combinacion.php
- AppServiceProvider
- Postulacion
- Combinacion
- EstrategiaResultado
- 2026_10_20_000000_agregar_fk_postulacion_a_asignaciones_prueba. pendiente.php
- 0001_01_01_000001_create_cache_table.php
- EstrategiasResultadoTest
- PostulacionController.php
- database/database/migrations/2026_10_09_000001_create_pruebas_table.php
- database/database/migrations/2026_10_09_000003_create_preguntas_table.php
- database/database/migrations/2026_10_09_000004_create_opciones_table.php
- database/database/migrations/2026_10_09_000005_create_opcion_dimension_table.php
- Illuminate\Support\Facades\Schema
- 2026_10_20_000000_add_metodo_dicotomia_combinada.php
- views.js
- Illuminate\Http\Request
- database/database/migrations/2026_10_09_000010_create_resultados_dimension_table.php
- database/migrations/2026_10_09_000002_create_dimensiones_table.php
- database/migrations/2026_10_09_000003_create_preguntas_table.php
- README.md
- database/migrations/2026_10_09_000005_create_opcion_dimension_table.php
- database/migrations/2026_10_09_000006_create_perfiles_table.php
- public/app/Services/Psicometria/MotorPsicometrico.php
- database/migrations/2026_10_09_000008_create_asignaciones_prueba_table.php
- database/migrations/2026_10_09_000011_create_resultados_prueba_table.php
- ResultadoDimension
- DimensionMayor
- web.php
- ResultadoFinal
- 2026_10_13_000001_add_dimension_padre_to_dimensiones_table.php
- 2026_10_20_000000_agregar_fk_postulacion_a_asignaciones_prueba.php
- database/database/migrations/2026_10_09_000011_create_resultados_prueba_table.php
- 2026_10_15_000001_add_metodo_patron_disc.php
- database/migrations/2026_10_09_000010_create_resultados_dimension_table.php
- Illuminate\Database\Migrations\Migration
- database/migrations/2026_10_09_000004_create_opciones_table.php
- CLAUDE.md
- login.blade.php
- contratista/index.blade.php
- show.blade.php
- postulante/index.blade.php
- 2026_10_15_000002_create_disc_clasificacion_table.php
- 2026_10_20_000001_add_username_to_users_table.php
- CorreoProveedorPermitido
- LoginRequest
- 0001_01_01_000002_create_jobs_table.php
- public/app/Services/Psicometria/Estrategias/DimensionMayor.php
- 2026_10_19_000000_create_rangos_interpretacion_table.php
- database/database/migrations/2026_10_09_000008_create_asignaciones_prueba_table.php
- formulario.blade.php
- database/database/migrations/2026_10_09_000009_create_respuestas_table.php

## God Nodes (most connected - your core abstractions)
1. `ResultadoFinal` - 34 edges
2. `ResultadoDimension` - 21 edges
3. `MotorPsicometrico` - 20 edges
4. `EstrategiaResultado` - 18 edges
5. `Combinacion` - 15 edges
6. `PatronDisc` - 15 edges
7. `DimensionMayor` - 14 edges
8. `PuntajePorDimension` - 14 edges
9. `AutenticacionTest` - 14 edges
10. `User` - 13 edges

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

## Communities (75 total, 60 thin omitted)

### Community 1 - "composer.json"
Cohesion: 0.04
Nodes (47): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+39 more)

### Community 2 - "MotorPsicometrico"
Cohesion: 0.06
Nodes (18): VerificarBarsit, VerificarBigFive, VerificarComunicacion, VerificarDisc, VerificarEscuchaActiva, VerificarKeirsey, MotorPsicometrico, BarsitSeeder (+10 more)

### Community 3 - "package.json"
Cohesion: 0.10
Nodes (19): devDependencies, axios, concurrently, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite, private (+11 more)

### Community 5 - "ResultadoFinal"
Cohesion: 0.11
Nodes (4): ResultadoFinal, EstrategiaResultado, PuntajePorDimension, PuntajeT

### Community 6 - "AutenticacionTest"
Cohesion: 0.07
Nodes (6): User, UserFactory, AutenticacionTest, ExampleTest, FlujoPostulacionTest, TestCase

### Community 7 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.13
Nodes (14): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}() (+6 more)

### Community 8 - "ResultadoDimension"
Cohesion: 0.07
Nodes (9): PatronDisc, PuntajeT, {closure#1}(), {closure#6}(), ResultadoDimension, {closure#1}(), UtilesEstrategia, PatronDiscTest (+1 more)

### Community 13 - "Postulacion"
Cohesion: 0.16
Nodes (3): AsignacionPrueba, Postulacion, Prueba

### Community 15 - "EstrategiaResultado"
Cohesion: 0.23
Nodes (6): DicotomiaCombinada, EstrategiaResultado, PuntajePorDimension, TotalCorrectas, UtilesEstrategia, TotalCorrectas

### Community 25 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.15
Nodes (3): {closure#1}(), {closure#1}(), {closure#1}()

### Community 27 - "views.js"
Cohesion: 0.14
Nodes (27): api, API_BASE, main, router(), clearWizard(), loadWizard(), saveWizard(), setVacanteId() (+19 more)

### Community 28 - "Illuminate\Http\Request"
Cohesion: 0.22
Nodes (3): AuthController, Controller, PostulanteController

### Community 32 - "README.md"
Cohesion: 0.22
Nodes (8): About Laravel, Code of Conduct, Contributing, Laravel Sponsors, Learning Laravel, License, Premium Partners, Security Vulnerabilities

### Community 54 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.15
Nodes (3): {closure#1}(), {closure#1}(), {closure#1}()

### Community 69 - "0001_01_01_000002_create_jobs_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

## Knowledge Gaps
- **8 isolated node(s):** `axios`, `concurrently`, `tailwindcss`, `layouts.app`, `layouts.app` (+3 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 263 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **60 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `AutenticacionTest` to `MotorPsicometrico`, `PostulacionController.php`?**
  _High betweenness centrality (0.038) - this node is a cross-community bridge._
- **What connects `axios`, `concurrently`, `tailwindcss` to the rest of the system?**
  _8 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.041666666666666664 - nodes in this community are weakly interconnected._
- **Why does `EstrategiaResultado` connect `EstrategiaResultado` to `public/app/Services/Psicometria/MotorPsicometrico.php`, `.estrategiaPara`, `ResultadoFinal`, `public/app/Services/Psicometria/Estrategias/DimensionMayor.php`, `DimensionMayor`, `ResultadoDimension`, `public/app/Services/Psicometria/Estrategias/Combinacion.php`, `Combinacion`?**
  _High betweenness centrality (0.028) - this node is a cross-community bridge._
- **Should `MotorPsicometrico` be split into smaller, more focused modules?**
  _Cohesion score 0.05706214689265537 - nodes in this community are weakly interconnected._
- **Why does `Postulacion` connect `Postulacion` to `web.php`, `PostulacionController.php`, `AutenticacionTest`?**
  _High betweenness centrality (0.024) - this node is a cross-community bridge._
- **Should `package.json` be split into smaller, more focused modules?**
  _Cohesion score 0.10476190476190476 - nodes in this community are weakly interconnected._