# Graph Report - robasa-1  (2026-10-19)

## Corpus Check
- 123 files · ~54,412 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 20 file(s) not represented in the graph (top: (none) 16, .example 1, .xml 1)

## Summary
- 631 nodes · 1030 edges · 68 communities (16 shown, 52 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 8 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `00f2c00b`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- EstrategiasResultadoTest
- composer.json
- User
- package.json
- ResultadoDimension
- ResultadoFinal
- AutenticacionTest
- Illuminate\Database\Schema\Blueprint
- PatronDisc
- database/migrations/2026_10_09_000001_create_pruebas_table.php
- bootstrap/app.php
- public/app/Services/Psicometria/Estrategias/Combinacion.php
- AppServiceProvider
- Postulacion
- Combinacion
- MotorPsicometrico
- 2026_10_20_000000_agregar_fk_postulacion_a_asignaciones_prueba. pendiente.php
- 0001_01_01_000001_create_cache_table.php
- EstrategiasResultadoTest
- .estrategiaPara
- database/database/migrations/2026_10_09_000001_create_pruebas_table.php
- Illuminate\Database\Migrations\Migration
- database/database/migrations/2026_10_09_000004_create_opciones_table.php
- database/database/migrations/2026_10_09_000005_create_opcion_dimension_table.php
- Illuminate\Support\Facades\Schema
- database/database/migrations/2026_10_09_000007_create_rangos_interpretacion_table.php
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
- 0001_01_01_000000_create_users_table.php
- ResultadoFinal
- 2026_10_13_000001_add_dimension_padre_to_dimensiones_table.php
- 2026_10_20_000000_agregar_fk_postulacion_a_asignaciones_prueba.php
- database/database/migrations/2026_10_09_000011_create_resultados_prueba_table.php
- 2026_10_15_000001_add_metodo_patron_disc.php
- database/migrations/2026_10_09_000010_create_resultados_dimension_table.php
- 2026_10_13_000002_create_baremos_table.php
- database/migrations/2026_10_09_000004_create_opciones_table.php
- CLAUDE.md
- login.blade.php
- contratista/index.blade.php
- show.blade.php
- postulante/index.blade.php
- 2026_10_15_000002_create_disc_clasificacion_table.php
- 2026_10_20_000001_add_username_to_users_table.php
- database/database/migrations/2026_10_09_000009_create_respuestas_table.php
- UtilesEstrategia

## God Nodes (most connected - your core abstractions)
1. `ResultadoFinal` - 32 edges
2. `ResultadoDimension` - 21 edges
3. `EstrategiaResultado` - 17 edges
4. `Combinacion` - 15 edges
5. `PatronDisc` - 15 edges
6. `DimensionMayor` - 14 edges
7. `PuntajePorDimension` - 14 edges
8. `MotorPsicometrico` - 14 edges
9. `AutenticacionTest` - 14 edges
10. `User` - 13 edges

## Surprising Connections (you probably didn't know these)
- `{closure#7}()` --references--> `ResultadoDimension`  [EXTRACTED]
  public/app/Services/Psicometria/MotorPsicometrico.php → app/Services/Psicometria/ResultadoDimension.php
- `Combinacion` --implements--> `EstrategiaResultado`  [EXTRACTED]
  public/app/Services/Psicometria/Estrategias/Combinacion.php → app/Services/Psicometria/Estrategias/EstrategiaResultado.php
- `Combinacion` --mixes_in--> `UtilesEstrategia`  [EXTRACTED]
  public/app/Services/Psicometria/Estrategias/Combinacion.php → app/Services/Psicometria/Estrategias/UtilesEstrategia.php
- `{closure#1}()` --references--> `ResultadoDimension`  [EXTRACTED]
  public/app/Services/Psicometria/Estrategias/UtilesEstrategia.php → app/Services/Psicometria/ResultadoDimension.php
- `DimensionMayor` --implements--> `EstrategiaResultado`  [EXTRACTED]
  public/app/Services/Psicometria/Estrategias/DimensionMayor.php → app/Services/Psicometria/Estrategias/EstrategiaResultado.php

## Import Cycles
- None detected.

## Communities (68 total, 52 thin omitted)

### Community 1 - "composer.json"
Cohesion: 0.04
Nodes (47): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+39 more)

### Community 2 - "User"
Cohesion: 0.07
Nodes (7): User, UserFactory, BigFiveSeeder, {closure#1}(), ComunicacionSeeder, DatabaseSeeder, DiscSeeder

### Community 3 - "package.json"
Cohesion: 0.10
Nodes (19): devDependencies, axios, concurrently, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite, private (+11 more)

### Community 4 - "ResultadoDimension"
Cohesion: 0.12
Nodes (6): PuntajeT, {closure#1}(), {closure#6}(), ResultadoDimension, {closure#1}(), PuntajeTTest

### Community 5 - "ResultadoFinal"
Cohesion: 0.09
Nodes (8): EstrategiaResultado, UtilesEstrategia, ResultadoFinal, DimensionMayor, EstrategiaResultado, PuntajePorDimension, PuntajeT, TotalCorrectas

### Community 6 - "AutenticacionTest"
Cohesion: 0.13
Nodes (4): AutenticacionTest, ExampleTest, FlujoPostulacionTest, TestCase

### Community 7 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.13
Nodes (14): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}() (+6 more)

### Community 13 - "Postulacion"
Cohesion: 0.12
Nodes (4): ContratistaController, AsignacionPrueba, Postulacion, Prueba

### Community 15 - "MotorPsicometrico"
Cohesion: 0.20
Nodes (4): VerificarBigFive, VerificarComunicacion, VerificarDisc, MotorPsicometrico

### Community 22 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.15
Nodes (3): {closure#1}(), {closure#1}(), {closure#1}()

### Community 25 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.15
Nodes (3): {closure#1}(), {closure#1}(), {closure#1}()

### Community 27 - "views.js"
Cohesion: 0.14
Nodes (27): api, API_BASE, main, router(), clearWizard(), loadWizard(), saveWizard(), setVacanteId() (+19 more)

### Community 28 - "Illuminate\Http\Request"
Cohesion: 0.05
Nodes (8): AuthController, Controller, PostulacionController, PostulanteController, EsContratista, LoginRequest, RegistroRequest, CorreoProveedorPermitido

### Community 32 - "README.md"
Cohesion: 0.22
Nodes (8): About Laravel, Code of Conduct, Contributing, Laravel Sponsors, Learning Laravel, License, Premium Partners, Security Vulnerabilities

### Community 40 - "DimensionMayor"
Cohesion: 0.14
Nodes (4): DimensionMayor, PuntajePorDimension, TotalCorrectas, ExampleTest

### Community 42 - "0001_01_01_000000_create_users_table.php"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

## Knowledge Gaps
- **7 isolated node(s):** `axios`, `concurrently`, `tailwindcss`, `layouts.app`, `layouts.app` (+2 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 254 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **52 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Postulacion` connect `Postulacion` to `User`, `AutenticacionTest`?**
  _High betweenness centrality (0.049) - this node is a cross-community bridge._
- **What connects `axios`, `concurrently`, `tailwindcss` to the rest of the system?**
  _7 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.041666666666666664 - nodes in this community are weakly interconnected._
- **Why does `User` connect `User` to `AutenticacionTest`?**
  _High betweenness centrality (0.039) - this node is a cross-community bridge._
- **Should `User` be split into smaller, more focused modules?**
  _Cohesion score 0.07179487179487179 - nodes in this community are weakly interconnected._
- **Why does `EstrategiaResultado` connect `ResultadoFinal` to `public/app/Services/Psicometria/MotorPsicometrico.php`, `ResultadoDimension`, `DimensionMayor`, `PatronDisc`, `public/app/Services/Psicometria/Estrategias/Combinacion.php`, `Combinacion`, `.estrategiaPara`?**
  _High betweenness centrality (0.029) - this node is a cross-community bridge._
- **Should `package.json` be split into smaller, more focused modules?**
  _Cohesion score 0.10476190476190476 - nodes in this community are weakly interconnected._