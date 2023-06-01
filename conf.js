require('ts-node').register({
  project: 'e2e/tsconfig.e2e.json' // if you have one
});
exports.config = {
  seleniumAddress: 'http://localhost:4444/wd/hub',
  specs: [
    //'src/app/barcie/barcie-indeling/barcie-indeling.component.spec.ts',
    './src/todo-spec.ts',
  ],
};
