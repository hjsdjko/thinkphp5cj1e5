const base = {
    get() {
        return {
            url : "http://localhost:8080/thinkphp5cj1e5/",
            name: "thinkphp5cj1e5",
            // 退出到首页链接
            indexUrl: 'http://localhost:8080/thinkphp5cj1e5/front/index.html'
        };
    },
    getProjectName(){
        return {
            projectName: "家具购物小程序"
        } 
    }
}
export default base
