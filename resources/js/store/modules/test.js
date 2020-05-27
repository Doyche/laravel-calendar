import axios from 'axios';

const state = {
    users: {},
};
const getters = {};
const actions = {
    /*getList({commit}, pageNumber){
        axios.get('/api/v1/user/all/?page=' + pageNumber)
            .then(response =>{
                commit("setUsers", response.data);
            })
    }*/
};
const mutations = {
    /*setUsers(state, data){
        state.users = data;
    },
    setCurrentPage(state, data){
        state.users.current_page = data;
    }*/
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}
