<template>
	<view class="confirm-page">
		<view class="page-header">
			<view class="nav-row">
				<view class="nav-back" @tap="goBack">
					<text class="iconfont icon-fanhui nav-back-icon"></text>
				</view>
				<text class="nav-title">确认订单</text>
				<view class="nav-link" @tap="openAddress">{{ isPickupMode || virtualType ? '门店信息' : '地址管理' }}</view>
			</view>

			<view v-if="loading" class="hero-card">
				<text class="hero-tag">实时同步</text>
				<text class="hero-title">正在加载订单确认信息...</text>
				<text class="hero-text">商品、地址、优惠与应付金额都会从真实接口同步。</text>
			</view>

			<view v-else-if="summaryReady" class="hero-card">
				<text class="hero-tag">订单预览</text>
				<text class="hero-title">{{ summaryTitle }}</text>
				<text class="hero-text">{{ summaryDesc }}</text>
			</view>
		</view>

		<scroll-view class="content-shell" scroll-y="true" show-scrollbar="false">
			<view v-if="errorMessage" class="section-card">
				<text class="section-title">订单加载失败</text>
				<text class="section-text">{{ errorMessage }}</text>
				<view class="single-btn" @tap="initializePage">重新加载</view>
			</view>

			<template v-else-if="summaryReady">
				<view v-if="!virtualType" class="section-card">
					<view class="section-head">
						<text class="section-title">收货方式</text>
						<text class="section-caption">{{ shippingModeLabel }}</text>
					</view>

					<view v-if="canChooseShipping" class="shipping-tabs">
						<view
							:class="['shipping-tab', { active: !isPickupMode }]"
							@tap="changeShippingMode('express')"
						>
							快递配送
						</view>
						<view
							:class="['shipping-tab', { active: isPickupMode }]"
							@tap="changeShippingMode('pickup')"
						>
							到店自提
						</view>
					</view>

					<view v-if="!isPickupMode" class="info-panel" @tap="openAddress">
						<template v-if="addressInfo">
							<text class="panel-title">{{ addressInfo.name }} {{ addressInfo.phone }}</text>
							<text class="panel-text">{{ addressInfo.address }}</text>
							<text v-if="addressInfo.default" class="panel-badge">默认地址</text>
						</template>
						<template v-else>
							<text class="panel-title">暂未选择收货地址</text>
							<text class="panel-text">请先新增或选择一个收货地址，系统会据此重新计算运费与优惠。</text>
						</template>
						<text class="panel-link">切换地址</text>
					</view>

					<view v-else class="pickup-wrap">
						<view class="info-panel" @tap="openStoreSelector">
							<template v-if="selectedStore">
								<text class="panel-title">{{ selectedStore.name }} {{ selectedStore.phone || '' }}</text>
								<text class="panel-text">{{ selectedStore.address }}{{ selectedStore.detailed_address ? '，' + selectedStore.detailed_address : '' }}</text>
							</template>
							<template v-else>
								<text class="panel-title">暂未选择自提门店</text>
								<text class="panel-text">请选择提货门店，门店信息会用于提交订单。</text>
							</template>
							<text class="panel-link">{{ storeLoading ? '加载中...' : '选择门店' }}</text>
						</view>

						<view class="pickup-fields">
							<view class="field-row">
								<text class="field-label">联系人</text>
								<input
									v-model="pickupName"
									class="field-input"
									type="text"
									placeholder="请输入提货联系人"
									placeholder-class="field-placeholder"
								/>
							</view>
							<view class="field-row">
								<text class="field-label">联系电话</text>
								<input
									v-model="pickupPhone"
									class="field-input"
									type="number"
									maxlength="11"
									placeholder="请输入联系电话"
									placeholder-class="field-placeholder"
								/>
							</view>
						</view>
					</view>
				</view>

				<view v-else class="section-card">
					<view class="section-head">
						<text class="section-title">收货方式</text>
						<text class="section-caption">虚拟商品</text>
					</view>
					<text class="section-text">当前订单为虚拟商品，无需填写收货地址，可直接提交并进入支付流程。</text>
				</view>

				<view class="section-card">
					<view class="section-head">
						<text class="section-title">商品信息</text>
						<text class="section-caption">共 {{ totalQuantity }} 件</text>
					</view>
					<view v-for="item in goodsList" :key="item.id || item.title" class="goods-card">
						<image class="goods-image" :src="item.image" mode="aspectFill"></image>
						<view class="goods-copy">
							<text class="goods-title">{{ item.title }}</text>
							<text class="goods-text">规格：{{ item.spec }}</text>
							<text class="goods-text">数量：{{ item.quantity }}</text>
							<text class="goods-text">单价：{{ item.price }}</text>
						</view>
					</view>
				</view>

				<view class="section-card">
					<view class="section-head">
						<text class="section-title">优惠与发票</text>
					</view>
					<view class="meta-list">
						<view class="meta-item" @tap="openCouponSelector">
							<text class="meta-label">优惠券</text>
							<text :class="['meta-value', { highlight: canPickCoupon }]">{{ couponDisplayText }}</text>
						</view>
						<view v-if="canUseIntegral" class="meta-item toggle-item">
							<view>
								<text class="meta-label">积分抵扣</text>
								<text class="meta-note">可用 {{ usableIntegral || 0 }} 积分</text>
							</view>
							<switch
								color="#1f5eff"
								:checked="useIntegral"
								:disabled="!usableIntegral"
								@change="toggleIntegral"
							/>
						</view>
						<view v-if="invoiceEnabled" class="meta-item" @tap="openInvoice">
							<text class="meta-label">发票信息</text>
							<text class="meta-value highlight">{{ invoiceTitle }}</text>
						</view>
						<view class="meta-item message-item">
							<text class="meta-label">买家留言</text>
							<textarea
								v-model="mark"
								class="message-input"
								maxlength="100"
								placeholder="填写备注信息，100 字以内"
								placeholder-class="message-placeholder"
							></textarea>
						</view>
					</view>
				</view>

				<view class="section-card">
					<view class="section-head">
						<text class="section-title">费用明细</text>
					</view>
					<view class="meta-list">
						<view class="meta-item">
							<text class="meta-label">商品金额</text>
							<text class="meta-value">{{ goodsAmountText }}</text>
						</view>
						<view class="meta-item">
							<text class="meta-label">配送运费</text>
							<text class="meta-value">{{ postageText }}</text>
						</view>
						<view v-if="couponPrice > 0" class="meta-item">
							<text class="meta-label">优惠券抵扣</text>
							<text class="meta-value discount-text">-{{ couponPriceText }}</text>
						</view>
						<view v-if="integralPrice > 0" class="meta-item">
							<text class="meta-label">积分抵扣</text>
							<text class="meta-value discount-text">-{{ integralPriceText }}</text>
						</view>
					</view>
				</view>

				<view class="section-card">
					<view class="section-head">
						<text class="section-title">下单说明</text>
					</view>
					<view class="tip-list">
						<text class="tip-item">1. 页面已接入真实确认订单、金额计算与创建订单接口。</text>
						<text class="tip-item">2. 切换地址、门店、配送方式、优惠券和积分后，会重新计算应付金额。</text>
						<text class="tip-item">3. 提交成功后会直接跳转到收银台继续支付。</text>
					</view>
				</view>
			</template>
		</scroll-view>

		<view class="bottom-bar">
			<view class="bottom-total">
				<text class="bottom-total-label">实付款</text>
				<text class="bottom-total-value">{{ totalPriceText }}</text>
			</view>
			<view :class="['submit-btn', { disabled: submitDisabled }]" @tap="submitOrder">
				{{ submitting ? '提交中...' : '提交订单' }}
			</view>
		</view>

		<view v-if="couponPopupVisible" class="coupon-mask" @tap="closeCouponSelector">
			<view class="coupon-sheet" @tap.stop>
				<view class="coupon-head">
					<text class="coupon-title">选择优惠券</text>
					<text class="coupon-close" @tap="closeCouponSelector">关闭</text>
				</view>
				<scroll-view class="coupon-scroll" scroll-y="true">
					<view class="coupon-item plain" @tap="selectCoupon(0)">
						<view class="coupon-copy">
							<text class="coupon-name">不使用优惠券</text>
							<text class="coupon-meta">保持当前价格，不使用优惠抵扣</text>
						</view>
						<text class="coupon-check">{{ couponId ? '选择' : '已选' }}</text>
					</view>
					<view
						v-for="item in couponOptions"
						:key="item.id"
						:class="['coupon-item', { active: Number(couponId) === Number(item.id) }]"
						@tap="selectCoupon(item.id)"
					>
						<view class="coupon-copy">
							<text class="coupon-name">{{ item.coupon_title || item.title || '优惠券' }}</text>
							<text class="coupon-meta">
								减 {{ formatPriceValue(item.coupon_price || 0) }}
								<span v-if="Number(item.use_min_price || 0) > 0">，满 {{ formatPriceValue(item.use_min_price) }} 可用</span>
							</text>
						</view>
						<text class="coupon-check">{{ Number(couponId) === Number(item.id) ? '已选' : '选择' }}</text>
					</view>
				</scroll-view>
			</view>
		</view>
	</view>
</template>

<script>
import { checkShipping, getCouponsOrderPrice, orderConfirm, orderCreate, postOrderComputed } from '@/api/order.js';
import { storeListApi } from '@/api/store.js';
import { getAddressDefault, getAddressDetail, invoiceDetail, invoiceList } from '@/api/user.js';
import {
	safeArray,
	formatPrice,
	mapAddressItem,
	mapInvoiceHeader,
	getErrorMessage
} from '@/utils/guanchun-adapter.js';

const ADDRESS_CACHE_KEY = 'guanchun_order_confirm_address';
const INVOICE_CACHE_KEY = 'guanchun_order_confirm_invoice';
const GOODS_FALLBACK_IMAGE = '../../activity/static/redt.jpg';

function mapConfirmGoods(raw) {
	var productInfo = (raw && raw.productInfo) || {};
	var attrInfo = productInfo.attrInfo || {};
	return {
		id: raw && raw.id ? raw.id : raw && raw.unique ? raw.unique : '',
		title: productInfo.store_name || '订单商品',
		spec: attrInfo.suk || productInfo.spec || '默认规格',
		quantity: Number(raw && raw.cart_num ? raw.cart_num : 1),
		price: formatPrice(attrInfo.price || productInfo.price || 0),
		image: attrInfo.image || productInfo.image || GOODS_FALLBACK_IMAGE
	};
}

function getPhoneValue(userInfo) {
	if (!userInfo) return '';
	if (userInfo.record_phone && userInfo.record_phone !== '0') return userInfo.record_phone;
	return userInfo.phone || '';
}

export default {
	data() {
		return {
			cartId: '',
			news: 1,
			loading: false,
			submitting: false,
			storeLoading: false,
			errorMessage: '',
			hasLoaded: false,
			orderKey: '',
			seckillId: 0,
			bargainId: 0,
			combinationId: 0,
			discountId: 0,
			advanceId: 0,
			virtualType: 0,
			mark: '',
			addressId: 0,
			addressInfo: null,
			invoiceId: '',
			invoiceInfo: null,
			invoiceEnabled: false,
			goodsList: [],
			rawCartList: [],
			userInfo: {},
			totalPrice: 0,
			goodsAmount: 0,
			postage: 0,
			couponPrice: 0,
			integralPrice: 0,
			baseOrderPrice: 0,
			couponId: 0,
			couponOptions: [],
			couponPopupVisible: false,
			useIntegral: false,
			usableIntegral: 0,
			integralOpen: false,
			validCount: null,
			noCoupon: 0,
			shippingCapability: 'express',
			shippingMode: 'express',
			selectedStore: null,
			storeList: [],
			pickupName: '',
			pickupPhone: ''
		};
	},
	computed: {
		summaryReady() {
			return this.hasLoaded && this.goodsList.length > 0;
		},
		summaryTitle() {
			if (!this.goodsList.length) return '确认本次下单信息';
			return this.goodsList.length > 1 ? this.goodsList[0].title + ' 等商品' : this.goodsList[0].title;
		},
		summaryDesc() {
			return '请确认收货方式、优惠信息和订单金额，提交后会进入支付流程。';
		},
		totalQuantity() {
			return this.goodsList.reduce(function(total, item) {
				return total + Number(item.quantity || 0);
			}, 0);
		},
		isPickupMode() {
			return this.shippingMode === 'pickup';
		},
		shippingTypeValue() {
			return this.isPickupMode ? 2 : 1;
		},
		canChooseShipping() {
			return this.shippingCapability === 'both';
		},
		shippingModeLabel() {
			return this.isPickupMode ? '到店自提' : '快递配送';
		},
		invoiceTitle() {
			if (!this.invoiceInfo) return '不开发票';
			return this.invoiceInfo.headerTypeLabel + this.invoiceInfo.invoiceTypeLabel + ' · ' + this.invoiceInfo.name;
		},
		couponDisplayText() {
			if (!this.canPickCoupon) return '当前订单不可使用';
			if (this.couponId) return this.selectedCouponTitle;
			if (!this.couponOptions.length) return '暂无可用优惠券';
			return '请选择优惠券';
		},
		selectedCouponTitle() {
			var current = this.couponOptions.find((item) => Number(item.id) === Number(this.couponId));
			return current ? current.coupon_title || current.title || '已选择优惠券' : '已选择优惠券';
		},
		canPickCoupon() {
			if (this.noCoupon) return false;
			if (this.bargainId || this.combinationId || this.seckillId || this.discountId || this.advanceId) return false;
			return true;
		},
		canUseIntegral() {
			if (!this.integralOpen) return false;
			if (this.bargainId || this.combinationId || this.seckillId || this.advanceId) return false;
			return true;
		},
		goodsAmountText() {
			return formatPrice(this.goodsAmount);
		},
		postageText() {
			return formatPrice(this.postage);
		},
		couponPriceText() {
			return formatPrice(this.couponPrice);
		},
		integralPriceText() {
			return formatPrice(this.integralPrice);
		},
		totalPriceText() {
			return formatPrice(this.totalPrice);
		},
		allowSubmitByValidity() {
			if (this.validCount === null) return true;
			if (this.discountId) return this.validCount === this.rawCartList.length;
			return this.validCount > 0;
		},
		submitDisabled() {
			if (this.loading || this.submitting || !this.orderKey || !this.allowSubmitByValidity) return true;
			if (!this.virtualType && !this.isPickupMode && !this.addressId) return true;
			if (this.isPickupMode && (!this.selectedStore || !this.selectedStore.id)) return true;
			if (this.isPickupMode && (!this.pickupName || !this.isValidPhone(this.pickupPhone))) return true;
			return false;
		}
	},
	onLoad(options) {
		var that = this;
		this.cartId = options && options.cartId ? options.cartId : '';
		this.news = !options || !options.new || options.new === '0' ? 0 : 1;
		if (!this.cartId) {
			this.errorMessage = '缺少购物车参数，暂时无法确认订单。';
			return;
		}
		uni.$off('handClick');
		uni.$on('handClick', function(res) {
			if (!res || !res.address) return;
			that.selectedStore = res.address;
			if (that.hasLoaded && that.isPickupMode) {
				that.recomputePrice({
					silent: true,
					refreshCoupons: true
				});
			}
		});
		this.initializePage();
	},
	onShow() {
		if (!this.hasLoaded) return;
		var changed = this.applyCachedSelections();
		if (!changed.addressChanged && !changed.invoiceChanged) return;
		if (changed.addressChanged && !this.virtualType && !this.isPickupMode) {
			this.recomputePrice({
				silent: true,
				refreshCoupons: true
			});
		}
	},
	onUnload() {
		uni.$off('handClick');
	},
	methods: {
		formatPriceValue(value) {
			return Number(value || 0).toFixed(2);
		},
		isValidPhone(value) {
			return /^1[3-9]\d{9}$/.test(String(value || ''));
		},
		goBack() {
			uni.navigateBack({
				fail: () => {
					uni.switchTab({
						url: '/pages/goods_cate/goods_cate'
					});
				}
			});
		},
		getFromType() {
			// #ifdef MP
			return 'routine';
			// #endif
			// #ifdef APP-PLUS
			return 'app';
			// #endif
			// #ifdef H5
			if (this.$wechat && typeof this.$wechat.isWeixin === 'function' && this.$wechat.isWeixin()) {
				return 'weixin';
			}
			return 'weixinh5';
			// #endif
			return 'weixinh5';
		},
		initializePage() {
			this.applyCachedSelections();
			this.loading = true;
			this.errorMessage = '';
			this.resolveShippingCapability()
				.then(() => {
					return this.fetchConfirmData();
				})
				.catch((error) => {
					this.errorMessage = getErrorMessage(error);
				})
				.finally(() => {
					this.loading = false;
				});
		},
		resolveShippingCapability() {
			return checkShipping(this.cartId, this.news)
				.then((res) => {
					var type = Number(res && res.data ? res.data.type : 1);
					if (type === 0) {
						this.shippingCapability = 'both';
						this.shippingMode = 'express';
						return;
					}
					if (type === 2) {
						this.shippingCapability = 'pickup';
						this.shippingMode = 'pickup';
						return this.ensureStoreList();
					}
					this.shippingCapability = 'express';
					this.shippingMode = 'express';
				})
				.catch(() => {
					this.shippingCapability = 'express';
					this.shippingMode = 'express';
				});
		},
		applyCachedSelections() {
			var result = {
				addressChanged: false,
				invoiceChanged: false
			};
			var cachedAddress = uni.getStorageSync(ADDRESS_CACHE_KEY);
			if (cachedAddress && cachedAddress.id) {
				this.addressId = Number(cachedAddress.id || 0);
				this.addressInfo = cachedAddress;
				uni.removeStorageSync(ADDRESS_CACHE_KEY);
				result.addressChanged = true;
			}
			var cachedInvoice = uni.getStorageSync(INVOICE_CACHE_KEY);
			if (cachedInvoice && cachedInvoice.id) {
				this.invoiceId = String(cachedInvoice.id);
				this.invoiceInfo = cachedInvoice;
				uni.removeStorageSync(INVOICE_CACHE_KEY);
				result.invoiceChanged = true;
			}
			return result;
		},
		fetchConfirmData() {
			return orderConfirm({
				cartId: this.cartId,
				new: this.news,
				addressId: this.isPickupMode || this.virtualType ? 0 : this.addressId,
				shipping_type: this.shippingTypeValue
			})
				.then((res) => {
					var data = res.data || {};
					this.userInfo = data.userInfo || {};
					this.orderKey = data.orderKey || '';
					this.virtualType = Number(data.virtual_type || 0);
					this.seckillId = Number(data.seckill_id || 0);
					this.discountId = Number(data.discount_id || 0);
					this.noCoupon = Number(data.noCoupon || 0);
					this.validCount = data.valid_count === undefined ? null : Number(data.valid_count || 0);
					this.invoiceEnabled = !!(Number(data.invoice_func || 0) || Number(data.special_invoice || 0));
					this.integralOpen = !!Number(data.integral_open || 0);
					this.usableIntegral = Number(data.usable_integral || 0);
					this.rawCartList = safeArray(data.cartInfo);
					this.goodsList = this.rawCartList.map(function(item) {
						return mapConfirmGoods(item);
					});
					this.pickupName = this.pickupName || this.userInfo.real_name || '';
					this.pickupPhone = this.pickupPhone || getPhoneValue(this.userInfo);
					this.syncCartFlags();
					this.applyPriceGroup(data.priceGroup || {});
					this.hasLoaded = true;
					return Promise.all([
						this.ensureAddressInfo(),
						this.ensureInvoiceInfo(),
						this.isPickupMode ? this.ensureStoreList() : Promise.resolve()
					]);
				})
				.then(() => {
					if (this.shouldRecomputeAfterLoad()) {
						return this.recomputePrice({
							silent: true,
							refreshCoupons: true
						});
					}
					return this.loadCouponList();
				})
				.catch((error) => {
					this.goodsList = [];
					this.rawCartList = [];
					this.orderKey = '';
					this.hasLoaded = false;
					throw error;
				});
		},
		shouldRecomputeAfterLoad() {
			if (!this.orderKey) return false;
			if (this.virtualType) return true;
			if (this.isPickupMode) return !!(this.selectedStore && this.selectedStore.id);
			return !!this.addressId;
		},
		syncCartFlags() {
			var bargainId = 0;
			var combinationId = 0;
			var advanceId = 0;
			this.rawCartList.forEach(function(item) {
				bargainId = bargainId || Number(item && item.bargain_id ? item.bargain_id : 0);
				combinationId = combinationId || Number(item && item.combination_id ? item.combination_id : 0);
				advanceId = advanceId || Number(item && item.advance_id ? item.advance_id : 0);
			});
			this.bargainId = bargainId;
			this.combinationId = combinationId;
			this.advanceId = advanceId;
		},
		applyPriceGroup(priceGroup) {
			this.goodsAmount = Number(priceGroup.totalPrice || 0);
			this.postage = this.isPickupMode ? 0 : Number(priceGroup.storePostage || 0);
			this.baseOrderPrice = this.goodsAmount + this.postage;
			this.totalPrice = this.baseOrderPrice;
			this.couponPrice = 0;
			this.integralPrice = 0;
		},
		ensureAddressInfo() {
			if (this.virtualType || this.isPickupMode) {
				return Promise.resolve();
			}
			if (this.addressInfo && this.addressId) {
				return Promise.resolve();
			}
			var request = this.addressId ? getAddressDetail(this.addressId) : getAddressDefault();
			return request
				.then((res) => {
					if (!res || !res.data || Array.isArray(res.data)) {
						this.addressInfo = null;
						this.addressId = 0;
						return;
					}
					var mapped = mapAddressItem(res.data);
					this.addressInfo = mapped;
					this.addressId = Number(mapped.id || 0);
				})
				.catch(() => {
					this.addressInfo = null;
					this.addressId = 0;
				});
		},
		ensureInvoiceInfo() {
			if (!this.invoiceEnabled) {
				this.invoiceInfo = null;
				this.invoiceId = '';
				return Promise.resolve();
			}
			if (this.invoiceInfo && this.invoiceId) {
				return Promise.resolve();
			}
			if (this.invoiceId) {
				return invoiceDetail(this.invoiceId)
					.then((res) => {
						this.invoiceInfo = mapInvoiceHeader(res.data || {});
						this.invoiceId = String(this.invoiceInfo.id || this.invoiceId);
					})
					.catch(() => {
						this.invoiceInfo = null;
						this.invoiceId = '';
					});
			}
			return invoiceList({ page: 1, limit: 20 })
				.then((res) => {
					var list = safeArray(res.data);
					var target = list.find(function(item) {
						return Number(item && item.is_default) === 1;
					});
					if (!target) return;
					this.invoiceInfo = mapInvoiceHeader(target);
					this.invoiceId = String(this.invoiceInfo.id || '');
				})
				.catch(() => {
					this.invoiceInfo = null;
					this.invoiceId = '';
				});
		},
		ensureStoreList() {
			if (this.storeLoading) return Promise.resolve();
			this.storeLoading = true;
			return new Promise((resolve) => {
				var longitude = uni.getStorageSync('user_longitude') || '';
				var latitude = uni.getStorageSync('user_latitude') || '';
				var done = function() {
					storeListApi({
						latitude: latitude,
						longitude: longitude,
						page: 1,
						limit: 10
					})
						.then((res) => {
							var list = (((res || {}).data || {}).list || {}).list || [];
							this.storeList = list;
							if (this.selectedStore && this.selectedStore.id) {
								var matched = list.find((item) => Number(item.id) === Number(this.selectedStore.id));
								this.selectedStore = matched || this.selectedStore;
							} else if (list.length) {
								this.selectedStore = list[0];
							}
						})
						.catch(() => {})
						.finally(() => {
							this.storeLoading = false;
							resolve();
						});
				}.bind(this);

				if (latitude && longitude) {
					done();
					return;
				}

				uni.getLocation({
					type: 'wgs84',
					success: function(res) {
						latitude = res.latitude;
						longitude = res.longitude;
						uni.setStorageSync('user_latitude', latitude);
						uni.setStorageSync('user_longitude', longitude);
					},
					complete: done
				});
			});
		},
		loadCouponList() {
			if (!this.canPickCoupon) {
				this.couponOptions = [];
				this.couponId = 0;
				return Promise.resolve();
			}
			return getCouponsOrderPrice(this.baseOrderPrice, {
				cartId: this.cartId,
				new: this.news,
				shippingType: this.shippingTypeValue
			})
				.then((res) => {
					var list = safeArray(res.data);
					this.couponOptions = list;
					if (!list.some((item) => Number(item.id) === Number(this.couponId))) {
						this.couponId = 0;
					}
				})
				.catch(() => {
					this.couponOptions = [];
					this.couponId = 0;
				});
		},
		recomputePrice(options) {
			var config = options || {};
			if (!this.orderKey) return Promise.resolve();
			return postOrderComputed(this.orderKey, {
				addressId: this.isPickupMode || this.virtualType ? 0 : this.addressId,
				useIntegral: this.useIntegral ? 1 : 0,
				couponId: this.couponId || 0,
				shipping_type: this.shippingTypeValue,
				payType: '',
				store_id: this.isPickupMode && this.selectedStore ? this.selectedStore.id : 0
			})
				.then((res) => {
					var result = res.data && res.data.result ? res.data.result : {};
					this.totalPrice = Number(result.pay_price || this.totalPrice || 0);
					this.couponPrice = Number(result.coupon_price || 0);
					this.integralPrice = Number(result.deduction_price || 0);
					this.postage = this.isPickupMode ? 0 : Number(result.pay_postage || 0);
					this.baseOrderPrice = this.totalPrice + this.couponPrice + this.integralPrice;
				})
				.then(() => {
					if (config.refreshCoupons) {
						return this.loadCouponList();
					}
					return null;
				})
				.catch((error) => {
					if (config.silent) return null;
					uni.showToast({
						title: getErrorMessage(error),
						icon: 'none'
					});
					return null;
				});
		},
		changeShippingMode(mode) {
			if (mode === this.shippingMode) return;
			this.shippingMode = mode;
			if (this.isPickupMode) {
				this.ensureStoreList().finally(() => {
					this.fetchShippingChangeData();
				});
				return;
			}
			this.fetchShippingChangeData();
		},
		fetchShippingChangeData() {
			this.loading = true;
			this.fetchConfirmData()
				.catch((error) => {
					uni.showToast({
						title: getErrorMessage(error),
						icon: 'none'
					});
				})
				.finally(() => {
					this.loading = false;
				});
		},
		toggleIntegral(e) {
			this.useIntegral = !!(e && e.detail ? e.detail.value : !this.useIntegral);
			this.recomputePrice({
				refreshCoupons: true
			});
		},
		openAddress() {
			if (this.isPickupMode || this.virtualType) {
				this.openStoreSelector();
				return;
			}
			uni.navigateTo({
				url: '/pages/users/user_address_list/index?mode=select&source=order_confirm'
			});
		},
		openInvoice() {
			uni.navigateTo({
				url: '/pages/users/user_invoice_list/index?mode=select&source=order_confirm'
			});
		},
		openStoreSelector() {
			uni.navigateTo({
				url: '/pages/goods/goods_details_store/index'
			});
		},
		openCouponSelector() {
			if (!this.canPickCoupon) {
				uni.showToast({
					title: '当前订单不可使用优惠券',
					icon: 'none'
				});
				return;
			}
			if (!this.couponOptions.length) {
				uni.showToast({
					title: '暂无可用优惠券',
					icon: 'none'
				});
				return;
			}
			this.couponPopupVisible = true;
		},
		closeCouponSelector() {
			this.couponPopupVisible = false;
		},
		selectCoupon(couponId) {
			this.couponId = Number(couponId || 0);
			this.couponPopupVisible = false;
			this.recomputePrice({
				refreshCoupons: false
			});
		},
		validateBeforeSubmit() {
			if (!this.allowSubmitByValidity) {
				uni.showToast({
					title: '当前商品状态不支持提交订单',
					icon: 'none'
				});
				return false;
			}
			if (!this.virtualType && !this.isPickupMode && !this.addressId) {
				uni.showToast({
					title: '请先选择收货地址',
					icon: 'none'
				});
				return false;
			}
			if (this.isPickupMode) {
				if (!this.selectedStore || !this.selectedStore.id) {
					uni.showToast({
						title: '请选择自提门店',
						icon: 'none'
					});
					return false;
				}
				if (!this.pickupName) {
					uni.showToast({
						title: '请输入提货联系人',
						icon: 'none'
					});
					return false;
				}
				if (!this.isValidPhone(this.pickupPhone)) {
					uni.showToast({
						title: '请输入正确的联系电话',
						icon: 'none'
					});
					return false;
				}
			}
			return true;
		},
		submitOrder() {
			if (this.submitDisabled && !this.validateBeforeSubmit()) {
				return;
			}
			if (!this.validateBeforeSubmit()) return;
			this.submitting = true;
			orderCreate(this.orderKey, {
				addressId: this.isPickupMode || this.virtualType ? 0 : this.addressId,
				formId: '',
				couponId: this.couponId || 0,
				useIntegral: this.useIntegral ? 1 : 0,
				bargainId: this.bargainId,
				combinationId: this.combinationId,
				discountId: this.discountId,
				pinkId: 0,
				advanceId: this.advanceId,
				seckill_id: this.seckillId,
				mark: this.mark,
				store_id: this.isPickupMode && this.selectedStore ? this.selectedStore.id : 0,
				real_name: this.isPickupMode ? this.pickupName : '',
				phone: this.isPickupMode ? this.pickupPhone : '',
				from: this.getFromType(),
				shipping_type: this.shippingTypeValue,
				new: this.news,
				invoice_id: this.invoiceId,
				// #ifdef H5
				quitUrl: location.protocol + '//' + location.host + '/pages/goods/order_list/index',
				// #endif
				// #ifdef APP-PLUS
				quitUrl: '/pages/goods/order_list/index'
				// #endif
			})
				.then((res) => {
					var result = (res.data && res.data.result) || {};
					var orderId = result.orderId || result.order_id || res.order_id || this.orderKey;
					uni.reLaunch({
						url: '/pages/goods/cashier/index?order_id=' + encodeURIComponent(orderId) + '&from_type=order'
					});
				})
				.catch((error) => {
					uni.showToast({
						title: getErrorMessage(error),
						icon: 'none'
					});
				})
				.finally(() => {
					this.submitting = false;
				});
		}
	}
};
</script>

<style scoped lang="scss">
.confirm-page {
	min-height: 100vh;
	padding-bottom: calc(120rpx + env(safe-area-inset-bottom));
	background:
		radial-gradient(circle at top right, rgba(22, 97, 255, 0.12), transparent 34%),
		linear-gradient(180deg, #f7fbff 0%, #f3f6fb 240rpx, #f4f6fa 100%);
}

.page-header {
	padding: calc(var(--status-bar-height) + 18rpx) 24rpx 0;
}

.nav-row {
	display: grid;
	grid-template-columns: 64rpx 1fr auto;
	align-items: center;
	margin-bottom: 22rpx;
}

.nav-back {
	display: flex;
	align-items: center;
	height: 56rpx;
}

.nav-back-icon {
	color: #182b52;
	font-size: 34rpx;
}

.nav-title {
	text-align: center;
	color: #182b52;
	font-size: 34rpx;
	font-weight: 700;
}

.nav-link {
	padding: 12rpx 20rpx;
	border-radius: 999rpx;
	background: rgba(255, 255, 255, 0.96);
	color: #1f5eff;
	font-size: 24rpx;
	font-weight: 600;
}

.hero-card {
	padding: 32rpx 30rpx;
	border-radius: 30rpx;
	background: linear-gradient(135deg, #1b60ff 0%, #6bb6ff 100%);
	box-shadow: 0 18rpx 34rpx rgba(38, 88, 190, 0.16);
}

.hero-tag,
.hero-title,
.hero-text {
	display: block;
	color: #ffffff;
}

.hero-tag {
	font-size: 22rpx;
	opacity: 0.78;
}

.hero-title {
	margin-top: 14rpx;
	font-size: 40rpx;
	font-weight: 700;
	line-height: 1.25;
}

.hero-text {
	margin-top: 12rpx;
	color: rgba(255, 255, 255, 0.84);
	font-size: 24rpx;
	line-height: 1.7;
}

.content-shell {
	height: calc(100vh - 230rpx - var(--status-bar-height));
	padding: 0 24rpx;
	box-sizing: border-box;
}

.section-card {
	margin-top: 20rpx;
	padding: 28rpx;
	border-radius: 28rpx;
	background: rgba(255, 255, 255, 0.96);
	box-shadow: 0 16rpx 30rpx rgba(35, 65, 130, 0.08);
}

.section-head {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 18rpx;
	margin-bottom: 22rpx;
}

.section-title,
.section-text,
.panel-title,
.panel-text,
.goods-title,
.goods-text,
.meta-label,
.meta-value,
.tip-item,
.bottom-total-label,
.bottom-total-value {
	display: block;
}

.section-title {
	color: #13213f;
	font-size: 32rpx;
	font-weight: 700;
}

.section-text {
	margin-top: 14rpx;
	color: #697792;
	font-size: 24rpx;
	line-height: 1.8;
}

.section-caption {
	color: #1f5eff;
	font-size: 24rpx;
}

.shipping-tabs {
	display: flex;
	gap: 16rpx;
	margin-bottom: 22rpx;
}

.shipping-tab {
	flex: 1;
	height: 84rpx;
	line-height: 84rpx;
	border-radius: 24rpx;
	background: #f2f6ff;
	color: #57709c;
	font-size: 28rpx;
	font-weight: 600;
	text-align: center;
}

.shipping-tab.active {
	background: linear-gradient(135deg, #1a61ff 0%, #5cb1ff 100%);
	color: #ffffff;
	box-shadow: 0 12rpx 24rpx rgba(31, 94, 255, 0.16);
}

.info-panel {
	position: relative;
	padding: 26rpx;
	border-radius: 24rpx;
	background: #f6f9ff;
}

.panel-title {
	color: #162648;
	font-size: 30rpx;
	font-weight: 700;
	line-height: 1.5;
	padding-right: 120rpx;
}

.panel-text {
	margin-top: 12rpx;
	color: #697792;
	font-size: 24rpx;
	line-height: 1.8;
	padding-right: 120rpx;
}

.panel-badge {
	display: inline-flex;
	margin-top: 16rpx;
	padding: 8rpx 14rpx;
	border-radius: 999rpx;
	background: rgba(31, 94, 255, 0.1);
	color: #1f5eff;
	font-size: 22rpx;
}

.panel-link {
	position: absolute;
	right: 26rpx;
	top: 30rpx;
	color: #1f5eff;
	font-size: 24rpx;
	font-weight: 600;
}

.pickup-wrap {
	display: flex;
	flex-direction: column;
	gap: 18rpx;
}

.pickup-fields {
	padding: 8rpx 24rpx;
	border-radius: 24rpx;
	background: #f8faff;
}

.field-row {
	display: flex;
	align-items: center;
	gap: 20rpx;
	min-height: 92rpx;
	border-bottom: 1rpx solid #e8eef8;
}

.field-row:last-child {
	border-bottom: 0;
}

.field-label {
	width: 120rpx;
	color: #7e8aa3;
	font-size: 24rpx;
}

.field-input {
	flex: 1;
	height: 92rpx;
	color: #1a2340;
	font-size: 26rpx;
}

.field-placeholder {
	color: #a0abc0;
	font-size: 24rpx;
}

.goods-card {
	display: flex;
	gap: 18rpx;
}

.goods-card + .goods-card {
	margin-top: 20rpx;
	padding-top: 20rpx;
	border-top: 1rpx solid #eef2fa;
}

.goods-image {
	width: 168rpx;
	height: 168rpx;
	border-radius: 22rpx;
	flex-shrink: 0;
}

.goods-copy {
	flex: 1;
}

.goods-title {
	color: #162648;
	font-size: 30rpx;
	font-weight: 700;
	line-height: 1.5;
}

.goods-text {
	margin-top: 10rpx;
	color: #6b7894;
	font-size: 22rpx;
	line-height: 1.7;
}

.meta-list {
	display: flex;
	flex-direction: column;
	gap: 16rpx;
}

.meta-item {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 20rpx;
	padding-bottom: 16rpx;
	border-bottom: 1rpx solid #eef2fa;
}

.meta-item:last-child {
	padding-bottom: 0;
	border-bottom: 0;
}

.meta-label {
	color: #7e8aa3;
	font-size: 24rpx;
}

.meta-value {
	flex: 1;
	text-align: right;
	color: #1a2340;
	font-size: 24rpx;
	font-weight: 600;
	line-height: 1.7;
}

.meta-value.highlight {
	color: #1f5eff;
}

.meta-note {
	display: block;
	margin-top: 8rpx;
	color: #97a6c1;
	font-size: 22rpx;
}

.discount-text {
	color: #ff5d44;
}

.toggle-item {
	align-items: flex-start;
}

.message-item {
	display: block;
}

.message-input {
	width: 100%;
	height: 140rpx;
	margin-top: 18rpx;
	padding: 22rpx;
	border-radius: 22rpx;
	background: #f6f8fc;
	box-sizing: border-box;
	font-size: 24rpx;
	color: #1a2340;
}

.message-placeholder {
	color: #a0abc0;
	font-size: 24rpx;
}

.tip-list {
	display: flex;
	flex-direction: column;
	gap: 14rpx;
}

.tip-item {
	color: #697792;
	font-size: 24rpx;
	line-height: 1.8;
}

.bottom-bar {
	position: fixed;
	left: 0;
	right: 0;
	bottom: 0;
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 18rpx 24rpx calc(18rpx + env(safe-area-inset-bottom));
	background: rgba(255, 255, 255, 0.96);
	box-shadow: 0 -10rpx 30rpx rgba(34, 61, 115, 0.08);
}

.bottom-total-label {
	color: #7c89a2;
	font-size: 22rpx;
}

.bottom-total-value {
	margin-top: 8rpx;
	color: #ff5d44;
	font-size: 40rpx;
	font-weight: 700;
}

.single-btn,
.submit-btn {
	display: flex;
	align-items: center;
	justify-content: center;
}

.single-btn {
	width: 220rpx;
	height: 76rpx;
	margin-top: 22rpx;
	border-radius: 999rpx;
	background: linear-gradient(135deg, #1a61ff 0%, #5cb1ff 100%);
	color: #fff;
	font-size: 26rpx;
	font-weight: 600;
}

.submit-btn {
	width: 280rpx;
	height: 84rpx;
	border-radius: 999rpx;
	background: linear-gradient(135deg, #1a61ff 0%, #5cb1ff 100%);
	color: #ffffff;
	font-size: 28rpx;
	font-weight: 600;
}

.submit-btn.disabled {
	opacity: 0.45;
}

.coupon-mask {
	position: fixed;
	inset: 0;
	display: flex;
	align-items: flex-end;
	background: rgba(13, 24, 46, 0.45);
	z-index: 99;
}

.coupon-sheet {
	width: 100%;
	max-height: 70vh;
	padding: 24rpx;
	border-radius: 32rpx 32rpx 0 0;
	background: #ffffff;
	box-sizing: border-box;
}

.coupon-head {
	display: flex;
	align-items: center;
	justify-content: space-between;
	margin-bottom: 20rpx;
}

.coupon-title {
	color: #13213f;
	font-size: 32rpx;
	font-weight: 700;
}

.coupon-close {
	color: #7d8da8;
	font-size: 24rpx;
}

.coupon-scroll {
	max-height: 56vh;
}

.coupon-item {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 18rpx;
	padding: 24rpx;
	border-radius: 24rpx;
	background: #f7faff;
	border: 1rpx solid transparent;
}

.coupon-item + .coupon-item {
	margin-top: 16rpx;
}

.coupon-item.active {
	border-color: rgba(31, 94, 255, 0.24);
	background: rgba(31, 94, 255, 0.06);
}

.coupon-item.plain {
	background: #f4f6fa;
}

.coupon-copy {
	flex: 1;
}

.coupon-name {
	display: block;
	color: #1b2948;
	font-size: 28rpx;
	font-weight: 700;
	line-height: 1.5;
}

.coupon-meta {
	display: block;
	margin-top: 10rpx;
	color: #6f7d98;
	font-size: 22rpx;
	line-height: 1.7;
}

.coupon-check {
	color: #1f5eff;
	font-size: 24rpx;
	font-weight: 600;
}
</style>
