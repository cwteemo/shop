import axios from 'axios'
export function request(config){
    //创建axios实例
    const instance = axios.create({
        baseURL: "http://192.168.1.105/manager/public/index.php/",
        timeout: 5000
    })

    instance.interceptors.request.use(config => {
        return config
    },err => {

    })

    instance.interceptors.response.use(res => {
        return res.data
    },err =>{

    })

    return instance(config)
}