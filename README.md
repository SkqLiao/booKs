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

### 2023-08-22

- [x] 增加书籍增加页面

### 2023-08-25

- [x] 增加书籍编辑功能

### 2023-08-28

- [x] 增加阅读日历页面

### 2023-08-31

- [x] 增加图书阅读记录模块
- [x] 增加图书增加阅读记录面板
- [x] 修复增加图书/更新图书信息时，作者和译者被更新为空串的bug

### 2023-09-02

- [x] 重构后端API

### 2023-09-04

- [x] CRUD 阅读记录表格

### 2023-09-06

- [x] 增加书摘面板，CRUD 书摘表格

### 2023-09-07

- [x] 修复/增加书籍封面上传更新的功能

### 2023-09-08

- [x] 书籍阅读状态页面及数据库构建（未完成）

### 2023-09-11

- [x] 书籍阅读状态页面及卡片布局

### 2023-09-12

- [x] 新增阅读数据总览页及子组件

### 2023-09-13

- [x] 修改阅读数据总览为年度数据总览
- [x] 录入当当/京东余下数据

### 2023-09-14

- [x] 优化增加书籍，获取数据时进制提交

### 2023-09-15

- [x] 增加轮播图页面
- [x] 增加购买记录页面

### 2023-09-16

- [x] 导入所有历史订单
- [x] 修改阅读记录表结构
