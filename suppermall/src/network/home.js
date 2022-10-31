import {request} from "@/network/request";

export function getHomeMultiData(){
    return request({
        url: '/user/department?username=manager'
    })
}