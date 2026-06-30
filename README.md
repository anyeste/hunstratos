# hunstratos

**HUN VA FDM** — Flight Data Monitoring for Hungarian Airlines Virtual
Monorepo containing the Stratos plugin (TypeScript/React) and the phpVMS backend module (PHP/Laravel).

## Structure

```
hunstratos/
├── packages/
│   └── hun-fdm/          ← Stratos plugin
├── modules/
│   └── HunFDM/           ← phpVMS v7 module (coming next)
├── docs/                 ← Architecture, parameter definitions
└── .github/workflows/    ← CI
```

## Stratos Plugin

### Requirements
- Node.js 20+
- pnpm 11+
- Stratos running with `--dev` flag

### Development
```bash
pnpm install
pnpm dev       # requires Stratos running with --dev
pnpm build     # production build
```

### How it works

1. **Background module** (`src/background/index.ts`) runs silently during the entire flight, sampling simulator telemetry via Socket.io and detecting exceedances against fleet-aware thresholds.
2. On `pirep:filing`, it calculates `Final Score = Stratos Landing Score − Σ deductions`, POSTs the full FDM report to the phpVMS `HunFDM` module, and PATCHes the PIREP `score_percent`.
3. **UI panel** (`src/ui/index.tsx`) shows a live parameter strip during flight and a post-flight score card after landing.

### Fleet profiles

| Profile | Types |
|---|---|
| NARROWBODY | A320, A321, A21NLR, B738, B38M |
| WIDEBODY | A359, B77W |
| FREIGHTER | A30F, B738F, B77F |
| REGIONAL | E175, E190, DH8D |
| GA | GA-Multiengine/Turboprop |

### Scoring

`Final Score = Stratos Landing Score (0-100) − Σ exceedance deductions`

Clamped to minimum 0. The final score is written back to the phpVMS PIREP `score_percent` field, resolving the Stratos score=0 rejection issue.

## phpVMS Module

See `modules/HunFDM/` — built next.
Endpoints: `POST /api/hunfdm/report`, `GET /api/hunfdm/reports/{pirepId}`

## License
MIT
