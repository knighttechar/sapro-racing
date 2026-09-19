<template>
  <div class="brand-logo cursor-pointer" @click="$emit('click')">
    <img src="../assets/sapro.jpeg" alt="SAPRO" class="brand-image" />
  </div>
</template>

<script setup>
defineEmits(['click'])
</script>

<style scoped>
.brand-logo {
  display: flex;
  align-items: center;
  gap: 12px;
  transition: all 0.3s ease;
}

.brand-logo:hover {
  opacity: 0.8;
  transform: scale(1.05);
}

.brand-image {
  height: 56px;
  width: auto;
  object-fit: contain;
  border-radius: 0px;
  transition: height 0.3s ease;
}

/* Optimización para móviles en brand-image */
@media (max-width: 599px) {
  .brand-image {
    height: 48px;
  }
}

@media (max-width: 360px) {
  .brand-image {
    height: 40px;
  }
}

.brand-text {
  display: flex;
  align-items: center;
  gap: 8px;
  line-height: 1;
}

.brand-main {
  font-size: 24px;
  font-weight: 900;
  letter-spacing: 2px;
  color: white;
}

.brand-separator {
  font-size: 18px;
  color: #00bfff;
  opacity: 0.6;
}

.brand-sub {
  font-size: 24px;
  font-weight: 300;
  letter-spacing: 3px;
  color: #00bfff;
}

/* Mobile styles */
@media (max-width: 599px) {
  .brand-text {
    flex-direction: column;
    gap: 0;
    align-items: flex-start;
  }

  .brand-main {
    font-size: 16px;
    letter-spacing: 1px;
  }

  .brand-separator {
    display: none;
  }

  .brand-sub {
    font-size: 12px;
    letter-spacing: 2px;
    margin-top: 2px;
  }
}
</style>
