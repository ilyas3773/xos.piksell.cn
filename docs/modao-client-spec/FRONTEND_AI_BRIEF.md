# 前端 AI 开发任务书

## 目标

- 根据 `modao-client-spec` 目录中的截图、页面文案和交互说明，完成客户端 H5/小程序风格前端页面开发。
- 优先保证页面结构、视觉层级、文案和交互链路与原型一致。
- 先按静态页面与本地假数据实现，再逐步替换真实接口。

## 现有资料

- 页面总数：46
- 页面截图目录：`docs/modao-client-spec/screenshots/`
- 页面明细数据：`docs/modao-client-spec/client_pages.json`
- 页面说明文档：`docs/modao-client-spec/README.md`
- 流程摘要：`docs/modao-client-spec/CLIENT_FLOW_SUMMARY.md`
- 原型歧义说明：`docs/modao-client-spec/CLIENT_PROTOTYPE_RISKS.md`
- 页面跳转总表：`docs/modao-client-spec/NAVIGATION_MAP.md`

## 推荐信息架构

- 认证与登录：登录、微信号登录、短信登录、密码登录、注册、忘记密码
- 商城与内容：首页、商品详情、搜索、商城、购物车、会员、商学院、关于国林
- 个人中心与订单：我的、普通订单、售后、OEM 订单、发票管理、地址管理
- 业务入口：企微聊天、工厂 OEM

## 推荐前端目录

```text
src/
  pages/
    auth/
    home/
    mall/
    cart/
    profile/
    orders/
    oem/
    content/
  components/
    common/
    product/
    order/
    oem/
  mock/
  router/
  services/
  stores/
```

## 推荐路由

- `/auth/login-sms` 对应第 1 页《登录》
- `/auth/login-wechat` 对应第 2 页《微信号登录》
- `/auth/login-sms` 对应第 3 页《登录》
- `/auth/register` 对应第 4 页《注册》
- `/auth/reset-password` 对应第 5 页《忘记密码》
- `/auth/login-password` 对应第 6 页《登录-密码登录》
- `/product/detail` 对应第 7 页《首页-商品详情》
- `/mall` 对应第 8 页《商城》
- `/cart` 对应第 9 页《采购车》
- `/profile` 对应第 10 页《我的》
- `/home` 对应第 11 页《首页》
- `/product/detail/submit` 对应第 12 页《首页-商品详情 -下单》
- `/search` 对应第 13 页《搜索》
- `/product/detail/yaotie` 对应第 14 页《首页-药贴》
- `/product/detail/yuntai` 对应第 15 页《首页-云台活血贴》
- `/profile/orders` 对应第 16 页《我的-我的订单》
- `/profile/orders/pending-payment` 对应第 17 页《我的-我的订单-待付款》
- `/profile/orders/pending-shipment` 对应第 18 页《我的-我的订单-待发货》
- `/profile/orders/pending-receipt` 对应第 19 页《我的-我的订单-待收货》
- `/profile/orders/pending-review` 对应第 20 页《我的-我的订单-待评价》
- `/profile/orders/after-sale` 对应第 21 页《我的-我的订单-售后》
- `/profile/orders/pending-shipment/refund` 对应第 22 页《我的-我的订单-待发货-申请退款》
- `/profile/orders/pending-shipment/refund` 对应第 23 页《我的-我的订单-待发货-申请退款》
- `/profile/orders/after-sale/returned` 对应第 24 页《我的-我的订单-售后 -已寄回》
- `/profile/orders/after-sale/fill-tracking` 对应第 25 页《我的-我的订单-售后 -填写单号》
- `/profile/orders/after-sale/refunded` 对应第 26 页《我的-我的订单-售后 -已退款》
- `/profile/orders/completed` 对应第 27 页《我的-我的订单-待评价-交易成功》
- `/profile/orders/review/edit` 对应第 28 页《我的-我的订单-待评价-评价》
- `/profile/oem-orders` 对应第 29 页《我的-OEM订单》
- `/profile/oem-orders/pending-confirm` 对应第 30 页《我的-OEM订单-待确认》
- `/profile/oem-orders/sample-records` 对应第 31 页《我的-OEM订单-样品记录》
- `/profile/oem-orders/design-confirm` 对应第 32 页《我的-OEM订单-设计确认》
- `/profile/oem-orders/contract` 对应第 33 页《我的-OEM订单-合同协议》
- `/profile/oem-orders/production` 对应第 34 页《我的-OEM订单-生产阶段》
- `/profile/oem-orders/production/pending-2` 对应第 35 页《我的-OEM订单-生产阶段 -待生产-2》
- `/profile/oem-orders/completed` 对应第 36 页《我的-OEM订单-已完成》
- `/profile/invoices` 对应第 37 页《我的-发票管理》
- `/profile/addresses` 对应第 38 页《我的-地址管理》
- `/product/detail/wholesale` 对应第 39 页《首页-商品详情 -我要批发》
- `/membership` 对应第 40 页《会员》
- `/academy` 对应第 41 页《商学院》
- `/about` 对应第 42 页《关于国林》
- `/chat/wecom` 对应第 43 页《企微-聊天界面》
- `/oem/factory` 对应第 44 页《工厂OEM》
- `/oem/factory/detail` 对应第 45 页《工厂OEM-1》
- `/product/detail/variant` 对应第 46 页《首页-商品详情 Copy 1》

## 核心复用组件

- `AppHeader`：标题、返回、右上角操作入口
- `BottomTabBar`：首页、商城、购物车、我的
- `ProductCard`：商品图、标题、价格、规格、销量、按钮
- `OrderCard`：状态标签、商品摘要、金额、操作按钮
- `OrderStatusTabs`：待付款、待发货、待收货、待评价、售后
- `PriceBlock`：零售价、批发价、运费、合计
- `AddressBlock`：收货人、手机号、地址
- `OemStepTimeline`：待确认、样品、设计确认、合同协议、生产阶段、已完成
- `EmptyState`：暂无数据、无批注、空购物车
- `PrimaryActionBar`：底部主按钮组，例如“加入采购车 / 立即购买 / 我要批发”

## 关键业务对象

- 用户：昵称、头像、手机号、会员等级、默认地址
- 商品：商品 ID、名称、封面、价格、规格、详情图、库存、销量
- 购物车项：商品、规格、数量、价格、小计
- 普通订单：订单号、状态、商品列表、金额、物流、售后信息
- OEM 订单：订单号、合作方式、需求信息、样品记录、设计确认、合同、生产进度
- 发票：抬头、税号、开户地址、默认标记
- 地址：收货人、手机号、省市区、详细地址、默认标记

## 页面模块拆分

### 认证与登录

- 第 1 页《登录》：建议路由 `/auth/login-sms`，截图 `screenshots/page-01.png`
- 第 2 页《微信号登录》：建议路由 `/auth/login-wechat`，截图 `screenshots/page-02.png`
- 第 3 页《登录》：建议路由 `/auth/login-sms`，截图 `screenshots/page-03.png`
- 第 4 页《注册》：建议路由 `/auth/register`，截图 `screenshots/page-04.png`
- 第 5 页《忘记密码》：建议路由 `/auth/reset-password`，截图 `screenshots/page-05.png`
- 第 6 页《登录-密码登录》：建议路由 `/auth/login-password`，截图 `screenshots/page-06.png`

### 商城与内容

- 第 7 页《首页-商品详情》：建议路由 `/product/detail`，截图 `screenshots/page-07.png`
- 第 8 页《商城》：建议路由 `/mall`，截图 `screenshots/page-08.png`
- 第 9 页《采购车》：建议路由 `/cart`，截图 `screenshots/page-09.png`
- 第 11 页《首页》：建议路由 `/home`，截图 `screenshots/page-11.png`
- 第 12 页《首页-商品详情 -下单》：建议路由 `/product/detail/submit`，截图 `screenshots/page-12.png`
- 第 13 页《搜索》：建议路由 `/search`，截图 `screenshots/page-13.png`
- 第 14 页《首页-药贴》：建议路由 `/product/detail/yaotie`，截图 `screenshots/page-14.png`
- 第 15 页《首页-云台活血贴》：建议路由 `/product/detail/yuntai`，截图 `screenshots/page-15.png`
- 第 39 页《首页-商品详情 -我要批发》：建议路由 `/product/detail/wholesale`，截图 `screenshots/page-39.png`
- 第 40 页《会员》：建议路由 `/membership`，截图 `screenshots/page-40.png`
- 第 41 页《商学院》：建议路由 `/academy`，截图 `screenshots/page-41.png`
- 第 42 页《关于国林》：建议路由 `/about`，截图 `screenshots/page-42.png`
- 第 43 页《企微-聊天界面》：建议路由 `/chat/wecom`，截图 `screenshots/page-43.png`
- 第 44 页《工厂OEM》：建议路由 `/oem/factory`，截图 `screenshots/page-44.png`
- 第 45 页《工厂OEM-1》：建议路由 `/oem/factory/detail`，截图 `screenshots/page-45.png`
- 第 46 页《首页-商品详情 Copy 1》：建议路由 `/product/detail/variant`，截图 `screenshots/page-46.png`

### 个人中心与订单

- 第 10 页《我的》：建议路由 `/profile`，截图 `screenshots/page-10.png`
- 第 16 页《我的-我的订单》：建议路由 `/profile/orders`，截图 `screenshots/page-16.png`
- 第 17 页《我的-我的订单-待付款》：建议路由 `/profile/orders/pending-payment`，截图 `screenshots/page-17.png`
- 第 18 页《我的-我的订单-待发货》：建议路由 `/profile/orders/pending-shipment`，截图 `screenshots/page-18.png`
- 第 19 页《我的-我的订单-待收货》：建议路由 `/profile/orders/pending-receipt`，截图 `screenshots/page-19.png`
- 第 20 页《我的-我的订单-待评价》：建议路由 `/profile/orders/pending-review`，截图 `screenshots/page-20.png`
- 第 21 页《我的-我的订单-售后》：建议路由 `/profile/orders/after-sale`，截图 `screenshots/page-21.png`
- 第 22 页《我的-我的订单-待发货-申请退款》：建议路由 `/profile/orders/pending-shipment/refund`，截图 `screenshots/page-22.png`
- 第 23 页《我的-我的订单-待发货-申请退款》：建议路由 `/profile/orders/pending-shipment/refund`，截图 `screenshots/page-23.png`
- 第 24 页《我的-我的订单-售后 -已寄回》：建议路由 `/profile/orders/after-sale/returned`，截图 `screenshots/page-24.png`
- 第 25 页《我的-我的订单-售后 -填写单号》：建议路由 `/profile/orders/after-sale/fill-tracking`，截图 `screenshots/page-25.png`
- 第 26 页《我的-我的订单-售后 -已退款》：建议路由 `/profile/orders/after-sale/refunded`，截图 `screenshots/page-26.png`
- 第 27 页《我的-我的订单-待评价-交易成功》：建议路由 `/profile/orders/completed`，截图 `screenshots/page-27.png`
- 第 28 页《我的-我的订单-待评价-评价》：建议路由 `/profile/orders/review/edit`，截图 `screenshots/page-28.png`
- 第 29 页《我的-OEM订单》：建议路由 `/profile/oem-orders`，截图 `screenshots/page-29.png`
- 第 30 页《我的-OEM订单-待确认》：建议路由 `/profile/oem-orders/pending-confirm`，截图 `screenshots/page-30.png`
- 第 31 页《我的-OEM订单-样品记录》：建议路由 `/profile/oem-orders/sample-records`，截图 `screenshots/page-31.png`
- 第 32 页《我的-OEM订单-设计确认》：建议路由 `/profile/oem-orders/design-confirm`，截图 `screenshots/page-32.png`
- 第 33 页《我的-OEM订单-合同协议》：建议路由 `/profile/oem-orders/contract`，截图 `screenshots/page-33.png`
- 第 34 页《我的-OEM订单-生产阶段》：建议路由 `/profile/oem-orders/production`，截图 `screenshots/page-34.png`
- 第 35 页《我的-OEM订单-生产阶段 -待生产-2》：建议路由 `/profile/oem-orders/production/pending-2`，截图 `screenshots/page-35.png`
- 第 36 页《我的-OEM订单-已完成》：建议路由 `/profile/oem-orders/completed`，截图 `screenshots/page-36.png`
- 第 37 页《我的-发票管理》：建议路由 `/profile/invoices`，截图 `screenshots/page-37.png`
- 第 38 页《我的-地址管理》：建议路由 `/profile/addresses`，截图 `screenshots/page-38.png`

## 开发优先级

- 第 1 阶段：登录链路、首页、商品详情、购物车、我的
- 第 2 阶段：普通订单全状态、售后链路
- 第 3 阶段：OEM 订单流程、发票管理、地址管理
- 第 4 阶段：会员、商学院、关于国林、企微聊天、工厂 OEM

## 高风险点

- 原型中有大量透明热点，视觉元素和真实可点击区域不一定完全重合，开发时应以交互文档为准。
- 一部分原型热点点击后没有明显 UI 变化，前端实现时需要与产品确认是否为占位交互。
- 订单页和 OEM 页有大量状态页，建议使用单一详情页 + 状态驱动渲染，而不是每个页面独立写死。

## 给前端 AI 的执行要求

- 先读取 `README.md` 中对应页面段落，再查看该页截图。
- 对照 `client_pages.json` 的 `interactions` 实现路由跳转。
- 样式优先贴近截图，不要自行改变层级、按钮位置和表单顺序。
- 对重复页面做组件复用，尤其是订单卡片、底部操作栏、顶部标题栏、OEM 进度模块。
- 接口未知时先补齐 TypeScript 类型和 mock 数据，不要阻塞页面搭建。
