# Stage 1: Build Angular frontend
FROM node:18-alpine as builder
WORKDIR /app

# Copy package.json and package-lock.json (if it exists) and install dependencies
COPY package*.json ./
RUN npm install

# Copy the rest of the application code
COPY . .

# Install Angular CLI globally for dev server
RUN npm install -g @angular/cli

# Expose port for Angular dev server
EXPOSE 4200 4200
 # Angular dev server port

# Start the Angular development server
CMD ["ng", "serve", "--host", "0.0.0.0"]  # Allow access from outside the container
