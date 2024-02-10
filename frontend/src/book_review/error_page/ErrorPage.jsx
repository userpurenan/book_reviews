import React from 'react';
import { Link } from 'react-router-dom';
import { Header } from '../header/Header';
import './ErrorPage.scss';

export const ErrorPage = () => {
  return (
    <div>
      <Header />
      <div className="error-page">
        <div className="error-page_float">
          <div className="error-page_message">
            このページはログインユーザーのみアクセス可能となっています。お手数おかけしますが、ログイン又はアカウント作成の方をお願いします
          </div>
          <Link to={'/login'}>
            <button className="navigete-login">ログイン画面へ</button>
          </Link>
        </div>
      </div>
    </div>
  );
};
