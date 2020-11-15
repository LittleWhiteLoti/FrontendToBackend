<template>
    <div>
        {{ res }}
    </div>
</template>

<script>

import axios from 'axios'

export default {
    async setup()
    {

        let fetch = async () =>
        {
            const response = await axios.get('http://localhost:80');

            return await response.data.body
        }

        let res = await fetch();

        //console.log(res);

        return { res }
    }
}
</script>