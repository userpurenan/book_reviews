import { configureStore } from '@reduxjs/toolkit';
import { authSlice } from './authSlice';
import { pagenationSlice } from './pagenationSlice';

export const store = configureStore({
  reducer: {
    auth: authSlice.reducer,
    pagenation: pagenationSlice.reducer,
  },
});
