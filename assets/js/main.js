import Vue from 'vue'
require.context('../scss/', true, /\.(sa|sc|c)ss$/)
require.context('./', true, /\.js$/)

const aaa = new Vue({data: {a: 1}})
