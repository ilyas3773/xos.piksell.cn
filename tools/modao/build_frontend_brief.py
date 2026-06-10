import json
from collections import defaultdict
from pathlib import Path


ROOT = Path(r"d:\wwwroot\xos.piksell.cn")
SPEC_DIR = ROOT / "docs" / "modao-client-spec"
JSON_PATH = SPEC_DIR / "client_pages.json"
BRIEF_PATH = SPEC_DIR / "FRONTEND_AI_BRIEF.md"
NAV_PATH = SPEC_DIR / "NAVIGATION_MAP.md"


AUTH_PAGES = {"登录", "微信号登录", "注册", "忘记密码", "登录-密码登录"}
COMMERCE_PREFIXES = ("首页", "商城", "采购车", "搜索", "会员", "商学院", "关于国林", "企微", "工厂OEM")
PROFILE_PREFIX = "我的"


def load_data():
    return json.loads(JSON_PATH.read_text(encoding="utf-8"))


def classify_page(name: str) -> str:
    if name in AUTH_PAGES:
        return "认证与登录"
    if name.startswith(PROFILE_PREFIX):
        return "个人中心与订单"
    if name.startswith(COMMERCE_PREFIXES):
        return "商城与内容"
    return "其他"


def route_for_page(name: str) -> str:
    mapping = {
        "登录": "/auth/login-sms",
        "微信号登录": "/auth/login-wechat",
        "注册": "/auth/register",
        "忘记密码": "/auth/reset-password",
        "登录-密码登录": "/auth/login-password",
        "首页": "/home",
        "首页-药贴": "/product/detail/yaotie",
        "首页-云台活血贴": "/product/detail/yuntai",
        "商城": "/mall",
        "采购车": "/cart",
        "搜索": "/search",
        "我的": "/profile",
        "会员": "/membership",
        "商学院": "/academy",
        "关于国林": "/about",
        "企微-聊天界面": "/chat/wecom",
        "工厂OEM": "/oem/factory",
        "工厂OEM-1": "/oem/factory/detail",
        "我的-发票管理": "/profile/invoices",
        "我的-地址管理": "/profile/addresses",
        "我的-我的订单": "/profile/orders",
        "我的-OEM订单": "/profile/oem-orders",
    }
    if name in mapping:
        return mapping[name]
    if name.startswith("首页-商品详情"):
        if "我要批发" in name:
            return "/product/detail/wholesale"
        if "下单" in name:
            return "/product/detail/submit"
        if "Copy" in name:
            return "/product/detail/variant"
        if "药贴" in name:
            return "/product/detail/yaotie"
        if "云台活血贴" in name:
            return "/product/detail/yuntai"
        return "/product/detail"
    if name.startswith("我的-我的订单-待付款"):
        return "/profile/orders/pending-payment"
    if name.startswith("我的-我的订单-待发货-申请退款"):
        return "/profile/orders/pending-shipment/refund"
    if name.startswith("我的-我的订单-待发货"):
        return "/profile/orders/pending-shipment"
    if name.startswith("我的-我的订单-待收货"):
        return "/profile/orders/pending-receipt"
    if name.startswith("我的-我的订单-待评价-交易成功"):
        return "/profile/orders/completed"
    if name.startswith("我的-我的订单-待评价-评价"):
        return "/profile/orders/review/edit"
    if name.startswith("我的-我的订单-待评价"):
        return "/profile/orders/pending-review"
    if name.startswith("我的-我的订单-售后 -填写单号"):
        return "/profile/orders/after-sale/fill-tracking"
    if name.startswith("我的-我的订单-售后 -已寄回"):
        return "/profile/orders/after-sale/returned"
    if name.startswith("我的-我的订单-售后 -已退款"):
        return "/profile/orders/after-sale/refunded"
    if name.startswith("我的-我的订单-售后"):
        return "/profile/orders/after-sale"
    if name.startswith("我的-OEM订单-待确认"):
        return "/profile/oem-orders/pending-confirm"
    if name.startswith("我的-OEM订单-样品记录"):
        return "/profile/oem-orders/sample-records"
    if name.startswith("我的-OEM订单-设计确认"):
        return "/profile/oem-orders/design-confirm"
    if name.startswith("我的-OEM订单-合同协议"):
        return "/profile/oem-orders/contract"
    if name.startswith("我的-OEM订单-生产阶段 -待生产-2"):
        return "/profile/oem-orders/production/pending-2"
    if name.startswith("我的-OEM订单-生产阶段"):
        return "/profile/oem-orders/production"
    if name.startswith("我的-OEM订单-已完成"):
        return "/profile/oem-orders/completed"
    return f"/prototype/page-{name}"


def group_pages(pages):
    grouped = defaultdict(list)
    for page in pages:
        grouped[classify_page(page["pageName"])].append(page)
    return grouped


def collect_navigation_rows(pages):
    rows = []
    for page in pages:
        for interaction in page.get("interactions", []):
            result = interaction.get("result", {})
            if result.get("type") != "navigate":
                continue
            rows.append(
                {
                    "source_no": page["pageNumber"],
                    "source_name": page["pageName"],
                    "label": interaction["label"],
                    "target_no": result.get("targetPageNumber"),
                    "target_name": result.get("targetPageName"),
                }
            )
    return rows


def build_brief(data):
    pages = data["pages"]
    grouped = group_pages(pages)
    lines = []
    lines.append("# 前端 AI 开发任务书")
    lines.append("")
    lines.append("## 目标")
    lines.append("")
    lines.append("- 根据 `modao-client-spec` 目录中的截图、页面文案和交互说明，完成客户端 H5/小程序风格前端页面开发。")
    lines.append("- 优先保证页面结构、视觉层级、文案和交互链路与原型一致。")
    lines.append("- 先按静态页面与本地假数据实现，再逐步替换真实接口。")
    lines.append("")
    lines.append("## 现有资料")
    lines.append("")
    lines.append("- 页面总数：46")
    lines.append("- 页面截图目录：`docs/modao-client-spec/screenshots/`")
    lines.append("- 页面明细数据：`docs/modao-client-spec/client_pages.json`")
    lines.append("- 页面说明文档：`docs/modao-client-spec/README.md`")
    lines.append("")
    lines.append("## 推荐信息架构")
    lines.append("")
    lines.append("- 认证与登录：登录、微信号登录、短信登录、密码登录、注册、忘记密码")
    lines.append("- 商城与内容：首页、商品详情、搜索、商城、购物车、会员、商学院、关于国林")
    lines.append("- 个人中心与订单：我的、普通订单、售后、OEM 订单、发票管理、地址管理")
    lines.append("- 业务入口：企微聊天、工厂 OEM")
    lines.append("")
    lines.append("## 推荐前端目录")
    lines.append("")
    lines.append("```text")
    lines.append("src/")
    lines.append("  pages/")
    lines.append("    auth/")
    lines.append("    home/")
    lines.append("    mall/")
    lines.append("    cart/")
    lines.append("    profile/")
    lines.append("    orders/")
    lines.append("    oem/")
    lines.append("    content/")
    lines.append("  components/")
    lines.append("    common/")
    lines.append("    product/")
    lines.append("    order/")
    lines.append("    oem/")
    lines.append("  mock/")
    lines.append("  router/")
    lines.append("  services/")
    lines.append("  stores/")
    lines.append("```")
    lines.append("")
    lines.append("## 推荐路由")
    lines.append("")
    for page in pages:
        lines.append(f"- `{route_for_page(page['pageName'])}` 对应第 {page['pageNumber']} 页《{page['pageName']}》")
    lines.append("")
    lines.append("## 核心复用组件")
    lines.append("")
    lines.append("- `AppHeader`：标题、返回、右上角操作入口")
    lines.append("- `BottomTabBar`：首页、商城、购物车、我的")
    lines.append("- `ProductCard`：商品图、标题、价格、规格、销量、按钮")
    lines.append("- `OrderCard`：状态标签、商品摘要、金额、操作按钮")
    lines.append("- `OrderStatusTabs`：待付款、待发货、待收货、待评价、售后")
    lines.append("- `PriceBlock`：零售价、批发价、运费、合计")
    lines.append("- `AddressBlock`：收货人、手机号、地址")
    lines.append("- `OemStepTimeline`：待确认、样品、设计确认、合同协议、生产阶段、已完成")
    lines.append("- `EmptyState`：暂无数据、无批注、空购物车")
    lines.append("- `PrimaryActionBar`：底部主按钮组，例如“加入采购车 / 立即购买 / 我要批发”")
    lines.append("")
    lines.append("## 关键业务对象")
    lines.append("")
    lines.append("- 用户：昵称、头像、手机号、会员等级、默认地址")
    lines.append("- 商品：商品 ID、名称、封面、价格、规格、详情图、库存、销量")
    lines.append("- 购物车项：商品、规格、数量、价格、小计")
    lines.append("- 普通订单：订单号、状态、商品列表、金额、物流、售后信息")
    lines.append("- OEM 订单：订单号、合作方式、需求信息、样品记录、设计确认、合同、生产进度")
    lines.append("- 发票：抬头、税号、开户地址、默认标记")
    lines.append("- 地址：收货人、手机号、省市区、详细地址、默认标记")
    lines.append("")
    lines.append("## 页面模块拆分")
    lines.append("")
    for group_name in ("认证与登录", "商城与内容", "个人中心与订单", "其他"):
        items = grouped.get(group_name, [])
        if not items:
            continue
        lines.append(f"### {group_name}")
        lines.append("")
        for page in items:
            lines.append(
                f"- 第 {page['pageNumber']} 页《{page['pageName']}》：建议路由 `{route_for_page(page['pageName'])}`，截图 `screenshots/{page['screenshotFile']}`"
            )
        lines.append("")
    lines.append("## 开发优先级")
    lines.append("")
    lines.append("- 第 1 阶段：登录链路、首页、商品详情、购物车、我的")
    lines.append("- 第 2 阶段：普通订单全状态、售后链路")
    lines.append("- 第 3 阶段：OEM 订单流程、发票管理、地址管理")
    lines.append("- 第 4 阶段：会员、商学院、关于国林、企微聊天、工厂 OEM")
    lines.append("")
    lines.append("## 高风险点")
    lines.append("")
    lines.append("- 原型中有大量透明热点，视觉元素和真实可点击区域不一定完全重合，开发时应以交互文档为准。")
    lines.append("- 一部分原型热点点击后没有明显 UI 变化，前端实现时需要与产品确认是否为占位交互。")
    lines.append("- 订单页和 OEM 页有大量状态页，建议使用单一详情页 + 状态驱动渲染，而不是每个页面独立写死。")
    lines.append("")
    lines.append("## 给前端 AI 的执行要求")
    lines.append("")
    lines.append("- 先读取 `README.md` 中对应页面段落，再查看该页截图。")
    lines.append("- 对照 `client_pages.json` 的 `interactions` 实现路由跳转。")
    lines.append("- 样式优先贴近截图，不要自行改变层级、按钮位置和表单顺序。")
    lines.append("- 对重复页面做组件复用，尤其是订单卡片、底部操作栏、顶部标题栏、OEM 进度模块。")
    lines.append("- 接口未知时先补齐 TypeScript 类型和 mock 数据，不要阻塞页面搭建。")
    lines.append("")
    return "\n".join(lines)


def build_navigation_map(data):
    lines = []
    lines.append("# 页面跳转总表")
    lines.append("")
    lines.append("| 来源页 | 热点标签 | 目标页 |")
    lines.append("| --- | --- | --- |")
    for row in collect_navigation_rows(data["pages"]):
        lines.append(
            f"| {row['source_no']:02d}. {row['source_name']} | {row['label']} | {row['target_no']:02d}. {row['target_name']} |"
        )
    lines.append("")
    return "\n".join(lines)


def main():
    data = load_data()
    BRIEF_PATH.write_text(build_brief(data), encoding="utf-8")
    NAV_PATH.write_text(build_navigation_map(data), encoding="utf-8")
    print("frontend brief generated")


if __name__ == "__main__":
    main()
