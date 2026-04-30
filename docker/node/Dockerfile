FROM node:18-alpine

WORKDIR /app

COPY admin-system/package*.json ./

RUN npm install

COPY admin-system/ ./

CMD ["npm", "run", "build"]