# 管理系统后台

基于 ThinkPHP 8 + Vue 3 + Ant Design Vue 构建的企业级低代码后台管理系统。

## 技术栈

### 后端
- **框架**: ThinkPHP 8.0
- **数据库**: MySQL 8.0
- **认证**: JWT (JSON Web Token)
- **容器**: Docker

### 前端
- **框架**: Vue 3.4
- **构建工具**: Vite 8.0
- **UI组件库**: Ant Design Vue 4.x
- **状态管理**: Pinia
- **路由**: Vue Router 4.x

## 功能特性

### 系统管理
- **用户管理**: 用户列表、创建、编辑、删除、密码重置
- **角色管理**: 角色列表、权限配置、创建、编辑、删除
- **权限管理**: 基于 RBAC 的权限控制体系
- **菜单管理**: 动态菜单配置、页面关联、对象绑定

### 对象管理
- **对象定义**: 动态数据表创建、字段配置
- **字段管理**: 支持多种字段类型（文本、数字、日期、关联等）
- **触发器管理**: 自定义业务逻辑触发

### 页面管理
- **页面配置**: 列表页、编辑页配置
- **显示列设置**: 自定义列表显示字段
- **按钮配置**: 自定义操作按钮组
- **导入导出**: 支持数据批量导入导出

### 数据管理
- **动态列表**: 根据对象配置动态渲染数据表格
- **条件搜索**: 支持多种条件搜索（等于、不等于、大于、小于等）
- **增删改查**: 完整的数据操作功能

## 项目结构

```
thinkphp-docker/
├── admin-system/              # 前端项目
│   ├── src/
│   │   ├── api/              # API 请求封装
│   │   ├── assets/           # 静态资源
│   │   ├── components/       # 公共组件
│   │   ├── layout/           # 布局组件
│   │   ├── router/           # 路由配置
│   │   ├── store/            # 状态管理
│   │   ├── utils/            # 工具函数
│   │   └── views/            # 页面视图
│   └── package.json
├── src/                      # 后端项目
│   ├── app/
│   │   ├── controller/       # 控制器
│   │   ├── middleware/       # 中间件
│   │   └── service/          # 服务层
│   ├── config/               # 配置文件
│   ├── database/             # 数据库迁移
│   └── route/                # 路由定义
├── docker/                   # Docker 配置
├── sql/                      # 初始化 SQL
└── docker-compose.yml        # 容器编排
```

## 快速开始

### 环境要求
- Docker >= 20.10
- Docker Compose >= 2.0

### 启动项目

```bash
# 进入项目目录
cd thinkphp-docker

# 启动容器（首次启动会自动构建镜像）
docker-compose up -d

# 查看启动状态
docker-compose ps
```

### 服务访问

| 服务 | 地址 | 说明 |
|------|------|------|
| 前端 | http://localhost:8080 | 管理后台入口 |
| 后端 | http://localhost:8080/api | API 接口 |
| 数据库 | localhost:3306 | MySQL |

### 默认账号

```
用户名: admin
密码: 123456
```

## 开发指南

### 前端开发

```bash
# 进入前端目录
cd admin-system

# 安装依赖
npm install

# 启动开发服务器
npm run dev

# 构建生产版本
npm run build
```

### 后端开发

```bash
# 安装依赖
composer install

# 启动开发服务器
php think run
```

## API 接口

### 认证接口
| 接口 | 方法 | 描述 |
|------|------|------|
| `/api/auth/login` | POST | 用户登录 |
| `/api/user/profile` | GET | 获取用户信息 |

### 对象接口
| 接口 | 方法 | 描述 |
|------|------|------|
| `/api/object/list` | GET | 获取对象列表 |
| `/api/object/create` | POST | 创建对象 |
| `/api/object/data` | GET | 获取数据列表 |
| `/api/object/data` | POST | 新增数据 |
| `/api/object/data/:id` | PUT | 更新数据 |
| `/api/object/data/:id/delete` | POST | 删除数据 |

## 数据库结构

### 核心数据表

| 表名 | 说明 |
|------|------|
| `users` | 用户表 |
| `roles` | 角色表 |
| `permissions` | 权限表 |
| `menus` | 菜单表 |
| `objects` | 对象表 |
| `object_fields` | 对象字段表 |
| `object_triggers` | 对象触发器表 |
| `pages` | 页面表 |

## 许可证

MIT License

## 贡献

欢迎提交 Issue 和 Pull Request！