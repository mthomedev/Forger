# ── Stage 1: build ──────────────────────────────────────────────────────────
FROM node:22-alpine AS builder

WORKDIR /app

# Copy dependency manifests first for better layer caching
COPY package.json package-lock.json ./

RUN npm ci

# Copy source and build
COPY . .

# VITE_API_URL can be overridden at build time via --build-arg
# or at deploy time via an env var (Render injects service env vars into the build)
ARG VITE_API_URL=${VITE_API_URL:-http://localhost:8000/api}
ENV VITE_API_URL=${VITE_API_URL}

RUN npm run build

# ── Stage 2: serve ───────────────────────────────────────────────────────────
FROM nginx:stable-alpine

# Remove default nginx static assets
RUN rm -rf /usr/share/nginx/html/*

# Copy built files from builder stage
COPY --from=builder /app/dist /usr/share/nginx/html

# Nginx config for Vue Router (HTML5 history mode)
COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
