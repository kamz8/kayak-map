# Dashboard Frontend Tests

Frontend tests use Vitest and Vue Test Utils for dashboard stores, composables, and UI components.

## Commands

```bash
npm test              # Vitest watch mode
npm test -- --run     # Single Vitest run when supported by the local npm/Vitest setup
npm run coverage      # Coverage report
```

## Structure

```text
tests/vitest/
├── setup.js
├── trailEditorRiverRoute.spec.js
├── store/
│   └── breadcrumbs.spec.js
└── README.md
```

## What To Test

- Vuex state initialization, getters, mutations, and actions.
- Dashboard composables and their behavior with real store interactions.
- UI components through rendered output and emitted events.
- Trail editor route/routing behavior with focused assertions.

## Example

```javascript
it('adds a breadcrumb update for a key', () => {
  breadcrumbsModule.mutations.UPDATE_BREADCRUMB_BY_KEY(state, {
    key: 'trail',
    updates: { text: 'Trail Name', to: '/trail/123' }
  })

  expect(state.updates).toEqual({
    trail: { text: 'Trail Name', to: '/trail/123' }
  })
})
```

## Best Practices

- Keep each test focused on one behavior.
- Use descriptive test names.
- Follow Arrange, Act, Assert.
- Reset state in `beforeEach()`.
- Prefer real modules and rendered components over testing mocks.

## Troubleshooting

- `Cannot find module '@vue/test-utils'`: run `npm install`.
- `ReferenceError: vi is not defined`: confirm Vitest globals are enabled in the test setup.
- Aliases such as `@`, `@dashboard`, or `@ui` fail: confirm Vite/Vitest alias configuration matches `vite.config.js`.
