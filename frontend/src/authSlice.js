import { Cookies } from 'react-cookie';
import { createSlice } from '@reduxjs/toolkit';

const cookie = new Cookies();

const initialState = {
  isSignIn: cookie.get('token') !== undefined && cookie.get('token') !== ''
};

export const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    signIn: (state) => {
      state.isSignIn = true;
    },
    signOut: (state) => {
      state.isSignIn = false;
    }
  }
});

export const { signIn, signOut } = authSlice.actions;
