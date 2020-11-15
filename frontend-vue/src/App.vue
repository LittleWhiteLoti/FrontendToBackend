<template>
    <Suspense>
        <template #default>
            <async-component />
        </template>
        <template #fallback>
            <div>
                Failed to fetch data
            </div>
        </template>
    </Suspense>
</template>

<script>
import AsyncComponent from './components/AsyncComponent.vue';

export default {

    name: 'App',
    components: {
        AsyncComponent
    }
};
</script>