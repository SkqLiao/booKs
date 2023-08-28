# Book Management System

Vue3 + Vite + TypeScript + Naive UI.

Based on [qs admin](https://github.com/zclzone/qs-admin).

## LOGS

### 2023/08/12

- [x] 导入京东历史订单数据【Python】
- [x] 完成douban-book-api和dnmp环境的配置
- [x] 后端add/del/upd/get API【PHP】
- [ ] node（vue3）环境配置
- [ ] 京东部分订单数据未处理：一条数据包含多本书，需要分离

### 2023/08/13

- [x] 获取京东订单的图书所有封面图片，完善get API

- [x] 主页面初步布局+分页（El-Pagination）
- [x] 详情页面初步布局（El-Dialog）
- [x] El-Rate无法显示星星的bug（fixed：08/14）
- [ ] 封面图片大小未统一

### 2023/08/14

- [x] 编辑数据界面
- [ ] 删除图书后，页面未更新

### 2023/08/15

- [x] 修复了编辑书籍中上传新的封面不生效的bug
- [x] 新增了删除书籍时的警告弹窗
- [x] 组件分离，将书籍详情和编辑书籍的dialog分离成单独的组件
- [x] 组件间通信，处理了删除书籍时中的各种情况
- [x] bug：author/translator的[String]类型可能需要处理

### 2023/08/17

- [x] 全新路由及页面布局（来自于开源项目）
- [x] 全新UI框架——Naive UI
- [x] 我的书架
- [x] 书籍详情
- [x] 书籍筛选（2023/08/21）

### 2023-08-18

- [x] 筛选书籍面板及后端接口
- [x] bug：关闭modal时的回调函数（fixed：2023/08/21）

### 2023-08-21

- [x] 解决关闭modal时的回调函数的bug
- [x] 增加BookParams的本地存储，用于实时刷新当前筛选书籍

## 2023-08-22

- [x] 增加书籍增加页面

### 2023-08-25

- [x] 增加书籍编辑功能

### 2023-08-28

- [x] 增加阅读日历页面
