const fs = require('fs');
const path = require('path');

const moduleName = process.argv[2];

if (!moduleName) {
    console.error('Please provide a module name');
    process.exit(1);
}

const baseDir = path.join(__dirname, '..', 'js', 'modules', moduleName);

const structure = {
    components: [],
    store: ['index.js'],
    pages: [`${capitalize(moduleName)}.vue`],
    router: ['index.js'],
};

const createStructure = (base, structure) => {
    Object.keys(structure).forEach((dir) => {
        const dirPath = path.join(base, dir);
        if (!fs.existsSync(dirPath)) {
            fs.mkdirSync(dirPath, { recursive: true });
            console.log(`Created directory: ${dirPath}`);
        }

        structure[dir].forEach((file) => {
            const filePath = path.join(dirPath, file);
            if (!fs.existsSync(filePath)) {
                fs.writeFileSync(filePath, getTemplate(file, moduleName));
                console.log(`Created file: ${filePath}`);
            }
        });
    });
};

const getTemplate = (file, moduleName) => {
    const name = capitalize(moduleName);

    if (file === 'index.js' && /store/.test(file)) {
        return `const state = {
  items: [],
};

const mutations = {
  SET_ITEMS(state, items) {
    state.items = items;
  }
};

const actions = {
  setItems({ commit }, items) {
    commit('SET_ITEMS', items);
  }
};

const getters = {
  items: state => state.items,
};

export default {
  state,
  mutations,
  actions,
  getters
};
`;
    }

    if (file === 'index.js' && /router/.test(file)) {
        return `import ${name} from '../pages/${name}.vue';

export default [
  {
    path: '/${moduleName}',
    component: ${name}
  }
];
`;
    }

    if (/\.vue$/.test(file)) {
        return `<template>
  <v-container>
    <h1>${name}</h1>
  </v-container>
</template>

<script>
export default {
  name: '${name}'
};
</script>

<style>
/* Styl dla tej strony */
</style>
`;
    }

    return '';
};

const capitalize = (s) => {
    if (typeof s !== 'string') return '';
    return s.charAt(0).toUpperCase() + s.slice(1);
};

createStructure(baseDir, structure);

console.log(`Module ${moduleName} created successfully.`);
